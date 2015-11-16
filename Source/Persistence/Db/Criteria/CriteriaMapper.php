<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\Criteria\Condition\AndCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\Condition;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\NotCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\OrCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\MemberCondition;
use Iddigital\Cms\Core\Model\Criteria\Criteria;
use Iddigital\Cms\Core\Model\Criteria\NestedProperty;
use Iddigital\Cms\Core\Model\Criteria\MemberOrdering;
use Iddigital\Cms\Core\Model\ICriteria;
use Iddigital\Cms\Core\Model\IValueObject;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ArrayReadModelMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Query;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Ordering;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\BinOp;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The criteria mapper class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapper
{
    /**
     * @var IObjectMapper
     */
    private $mapper;

    /**
     * @var FinalizedMapperDefinition
     */
    private $definition;

    /**
     * @var Table
     */
    private $primaryTable;

    /**
     * @var string[]
     */
    private $propertyColumnMap;

    /**
     * @var callable[]
     */
    private $phpToDbPropertyConverterMap;

    /**
     * @var IRelation[]
     */
    private $relations;

    /**
     * @var EmbeddedObjectRelation[]
     */
    private $embeddedObjects = [];

    /**
     * CriteriaMapper constructor.
     *
     * @param IObjectMapper $mapper
     */
    public function __construct(IObjectMapper $mapper)
    {
        $mapper->initializeRelations();

        $this->mapper                      = $mapper;
        $this->definition                  = $this->mapper->getDefinition();
        $this->primaryTable                = $this->mapper->getDefinition()->getTable();
        $this->propertyColumnMap           = $this->definition->getPropertyColumnMap();
        $this->phpToDbPropertyConverterMap = $this->definition->getPhpToDbPropertyConverterMap();
        $this->relations                   = $this->definition->getPropertyRelationMap();

        foreach ($this->relations as $property => $relation) {
            if ($relation instanceof EmbeddedObjectRelation) {
                $this->embeddedObjects[$property] = $relation;
            }
        }
    }

    /**
     * @return IObjectMapper
     */
    final public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return FinalizedClassDefinition
     */
    protected function getMappedObjectType()
    {
        if ($this->mapper instanceof ArrayReadModelMapper) {
            return $this->mapper->getParentMapper()->getDefinition()->getClass();
        } else {
            return $this->mapper->getDefinition()->getClass();
        }
    }

    /**
     * @return Criteria
     */
    public function newCriteria()
    {
        return new Criteria($this->getMappedObjectType());
    }

    /**
     * Maps the supplied criteria to a select query for the entity.
     *
     * @param ICriteria $criteria
     *
     * @return Select
     */
    public function mapCriteriaToSelect(ICriteria $criteria)
    {
        $criteria->verifyOfClass($this->getMappedObjectType()->getClassName());

        $select = Select::from($this->primaryTable);

        $loaded = false;

        if ($criteria->hasCondition()) {
            $condition = $this->applySpecificInstanceOfCondition(
                    $criteria->getCondition(),
                    $select,
                    /* out */
                    $loaded
            );

            if ($condition) {
                $select->where($this->mapCondition($condition, $select));
            }
        }

        if (!$loaded) {
            $this->mapper->getMapping()->addLoadToSelect($select);
        }

        foreach ($criteria->getOrderings() as $ordering) {
            $select->orderBy($this->mapOrdering($ordering));
        }

        $select->offset($criteria->getStartOffset())
                ->limit($criteria->getLimitAmount());

        return $select;
    }

    private function applySpecificInstanceOfCondition(Condition $condition, Select $select, &$loaded)
    {
        if ($condition instanceof InstanceOfCondition) {
            $class = $condition->getClass();

            if ($class === $this->mapper->getObjectType()) {
                return null;
            }

            $this->mapper->getMapping()->addSpecificLoadToQuery($select, $class);
            $loaded = true;

            return null;
        } elseif ($condition instanceof AndCondition) {
            $innerConditions = $condition->getConditions();

            foreach ($innerConditions as $key => $condition) {
                $innerConditions[$key] = $this->applySpecificInstanceOfCondition($condition, $select, $loaded);
            }

            $innerConditions = array_filter($innerConditions);

            return count($innerConditions) === 1 ? reset($innerConditions) : new AndCondition($innerConditions);
        } else {
            return $condition;
        }
    }

    protected function mapOrdering(MemberOrdering $ordering)
    {
        return new Ordering(
                $this->mapProperty($ordering->getNestedMembers()),
                $ordering->isAsc() ? Ordering::ASC : Ordering::DESC
        );
    }

    private function mapCondition(Condition $condition, Select $select)
    {
        if ($condition instanceof AndCondition) {
            $expressions = [];
            foreach ($condition->getConditions() as $condition) {
                $expressions[] = $this->mapCondition($condition, $select);
            }

            return Expr::compoundAnd($expressions);
        } elseif ($condition instanceof OrCondition) {
            $expressions = [];
            foreach ($condition->getConditions() as $condition) {
                $expressions[] = $this->mapCondition($condition, $select);
            }

            return Expr::compoundOr($expressions);
        } elseif ($condition instanceof MemberCondition) {
            return $this->mapPropertyCondition($condition, $select);
        } elseif ($condition instanceof InstanceOfCondition) {
            return $this->mapper->getMapping()->getClassConditionExpr($select, $condition->getClass());
        } elseif ($condition instanceof NotCondition) {
            return Expr::not($this->mapCondition($condition->getCondition(), $select));
        }

        throw InvalidArgumentException::format(
                'Unknown condition type %s', get_class($condition)
        );
    }

    private function mapPropertyCondition(MemberCondition $condition, Select $select)
    {
        $properties   = $condition->getNestedMembers();
        $property     = array_shift($properties);
        $propertyName = $property->getName();

        if (count($properties) >= 1) {
            if (!isset($this->embeddedObjects[$propertyName])) {
                throw InvalidOperationException::format(
                        'Object criteria cannot be mapped to select: property \'%s\' must be mapped to an embedded object',
                        $propertyName
                );
            }

            return $this->createEmbeddedMapper($this->embeddedObjects[$propertyName])->mapPropertyCondition(
                    new MemberCondition(
                            new NestedProperty($properties),
                            $condition->getOperator(),
                            $condition->getValue()
                    ),
                    $select
            );
        }

        if (isset($this->propertyColumnMap[$propertyName])) {
            return $this->mapConditionToColumnExpression(
                    $property,
                    $condition->getOperator(),
                    $condition->getValue(),
                    $this->primaryTable->findColumn($this->propertyColumnMap[$propertyName])
            );
        } elseif (isset($this->embeddedObjects[$propertyName])) {
            return $this->mapConditionToEmbeddedObject(
                    $property,
                    $condition->getOperator(),
                    $condition->getValue(),
                    $this->embeddedObjects[$propertyName],
                    $select
            );
        }

        throw InvalidOperationException::format(
                'Object criteria cannot be mapped to select: property \'%s\' of type \'%s\' is not set to a mappable condition ',
                $property->getName(), $property->getType()->asTypeString()
        );
    }

    private function mapConditionToColumnExpression(
            FinalizedPropertyDefinition $property,
            $operator,
            $value,
            Column $column
    ) {
        static $dbOperatorMap = [
                ConditionOperator::EQUALS                           => BinOp::EQUAL,
                ConditionOperator::NOT_EQUALS                       => BinOp::NOT_EQUAL,
                ConditionOperator::IN                               => BinOp::IN,
                ConditionOperator::NOT_IN                           => BinOp::NOT_IN,
                ConditionOperator::LESS_THAN                        => BinOp::LESS_THAN,
                ConditionOperator::LESS_THAN_OR_EQUAL               => BinOp::LESS_THAN_OR_EQUAL,
                ConditionOperator::GREATER_THAN                     => BinOp::GREATER_THAN,
                ConditionOperator::GREATER_THAN_OR_EQUAL            => BinOp::GREATER_THAN_OR_EQUAL,
                ConditionOperator::STRING_CONTAINS                  => BinOp::STR_CONTAINS,
                ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => BinOp::STR_CONTAINS_CASE_INSENSITIVE,
        ];

        $propertyName = $property->getName();
        $columnType   = $column->getType();
        $column       = Expr::tableColumn($this->primaryTable, $column->getName());

        if (isset($this->phpToDbPropertyConverterMap[$propertyName])) {
            $value = call_user_func($this->phpToDbPropertyConverterMap[$propertyName], $value);
        }

        if ($operator === ConditionOperator::EQUALS && $value === null) {
            return Expr::isNull($column);
        }

        if ($operator === ConditionOperator::NOT_EQUALS && $value === null) {
            return Expr::isNotNull($column);
        }


        if ($operator === ConditionOperator::IN || $operator === ConditionOperator::NOT_IN) {
            $elements = [];
            foreach ($value as $element) {
                $elements[] = Expr::param($columnType, $element);
            }

            $param = Expr::tuple($elements);
        } else {
            $param = Expr::param($columnType, $value);
        }


        return new BinOp(
                $column,
                $dbOperatorMap[$operator],
                $param
        );
    }

    private function mapProperty(array $properties)
    {
        $propertyName = array_shift($properties)->getName();

        if (isset($this->propertyColumnMap[$propertyName])) {
            return Expr::tableColumn(
                    $this->primaryTable,
                    $this->propertyColumnMap[$propertyName]
            );
        } elseif (isset($this->embeddedObjects[$propertyName])) {
            $relation        = $this->embeddedObjects[$propertyName];
            $embeddedColumns = $relation->getMapper()->getDefinition()->getTable()->getColumnNames();

            if (count($properties) >= 1) {
                $embeddedMapper = $this->createEmbeddedMapper($relation);

                return $embeddedMapper->mapProperty($properties);
            }

            if (count($embeddedColumns) === 1) {
                return Expr::tableColumn(
                        $this->primaryTable,
                        reset($embeddedColumns)
                );
            }
        }

        throw InvalidOperationException::format(
                'Object criteria cannot be mapped to select: property \'%s\' is not mapped to column on table \'%s\'',
                $propertyName, $this->primaryTable->getName()
        );
    }

    /**
     * @param FinalizedPropertyDefinition $property
     * @param string                      $operator
     * @param mixed                       $value
     * @param EmbeddedObjectRelation      $relation
     * @param Select                      $select
     *
     * @return BinOp|Query\Expression\UnaryOp
     * @throws InvalidOperationException
     * @throws \Iddigital\Cms\Core\Exception\InvalidArgumentException
     * @internal param PropertyCondition $condition
     */
    private function mapConditionToEmbeddedObject(
            FinalizedPropertyDefinition $property,
            $operator,
            $value,
            EmbeddedObjectRelation $relation,
            Select $select
    ) {
        static $dbOperatorMap = [
                ConditionOperator::EQUALS     => BinOp::EQUAL,
                ConditionOperator::NOT_EQUALS => BinOp::NOT_EQUAL,
        ];

        $embeddedColumns = $relation->getMapper()->getDefinition()->getTable()->getColumns();

        if (count($embeddedColumns) === 1) {
            $dbOperatorMap += [
                    ConditionOperator::IN                               => BinOp::IN,
                    ConditionOperator::NOT_IN                           => BinOp::NOT_IN,
                    ConditionOperator::LESS_THAN                        => BinOp::LESS_THAN,
                    ConditionOperator::LESS_THAN_OR_EQUAL               => BinOp::LESS_THAN_OR_EQUAL,
                    ConditionOperator::GREATER_THAN                     => BinOp::GREATER_THAN,
                    ConditionOperator::GREATER_THAN_OR_EQUAL            => BinOp::GREATER_THAN_OR_EQUAL,
                    ConditionOperator::STRING_CONTAINS                  => BinOp::STR_CONTAINS,
                    ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => BinOp::STR_CONTAINS_CASE_INSENSITIVE,
            ];
        }

        if (!isset($dbOperatorMap[$operator])) {
            throw InvalidOperationException::format(
                    'Object criteria cannot be mapped to select: property %s::$%s of type \'%s\' does not support the \'%s\' operator',
                    $property->getAccessibility()->getDeclaredClass(), $property->getName(), $property->getType()->asTypeString(), $operator
            );
        }

        $dbOperator = $dbOperatorMap[$operator];

        /** @var IValueObject|null $value */
        if ($value === null) {
            if ($relation->getObjectIssetColumnName() !== null) {
                $issetColumn = Expr::tableColumn($this->primaryTable, $relation->getObjectIssetColumnName());

                if ($relation->issetColumnIsWithinValueObject()) {
                    return $dbOperator === BinOp::EQUAL
                            ? Expr::isNull($issetColumn)
                            : Expr::isNotNull($issetColumn);
                } else {
                    $columnType = $issetColumn->getColumn()->getType();

                    return $dbOperator === BinOp::EQUAL
                            ? Expr::equal($issetColumn, Expr::param($columnType, false))
                            : Expr::equal($issetColumn, Expr::param($columnType, true));
                }
            }

            return Expr::false();
        }

        $embeddedMapper     = $this->createEmbeddedMapper($relation);
        $embeddedProperties = $value->toArray();
        $embeddedDefinition = $relation->getMapper()->getDefinition()->getClass();
        $columnExpressions  = [];

        foreach ($embeddedDefinition->getProperties() as $property) {
            $columnExpressions[] = $embeddedMapper->mapPropertyCondition(
                    new MemberCondition(
                            new NestedProperty([$property]),
                            $operator,
                            $embeddedProperties[$property->getName()]
                    ),
                    $select
            );
        }

        return empty($columnExpressions) ? Expr::false() : Expr::compoundAnd($columnExpressions);
    }

    /**
     * @param EmbeddedObjectRelation $relation
     *
     * @return CriteriaMapper
     */
    private function createEmbeddedMapper(EmbeddedObjectRelation $relation)
    {
        $embeddedMapper               = new self($relation->getMapper());
        $embeddedMapper->primaryTable = $this->primaryTable;

        return $embeddedMapper;
    }
}