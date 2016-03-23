<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Persistence\Db\Criteria\MemberExpressionMappingException;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\EmbeddedParentObjectMapping;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToOneMemberRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Expression\BinOp;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Util\Debug;

/**
 * The to-one embedded object relation mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneEmbeddedObjectMapping extends ToOneRelationMapping implements IFinalRelationMemberMapping
{
    /**
     * @var EmbeddedObjectRelation
     */
    protected $relation;

    /**
     * ToOneEmbeddedObjectMapping constructor.
     *
     * @param IEntityMapper          $rootEntityMapper
     * @param IRelation[]            $relationsToSubSelect
     * @param EmbeddedObjectRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect, EmbeddedObjectRelation $relation)
    {
        parent::__construct($rootEntityMapper, $relationsToSubSelect, $relation);
    }

    /**
     * @inheritDoc
     */
    public function asMemberRelation() : MemberRelation
    {
        return new ToOneMemberRelation($this);
    }

    /**
     * @return bool
     */
    protected function isSingleColumnObject() : bool
    {
        return $this->getSingleColumn() !== null;
    }

    /**
     * @return Column|null
     */
    protected function getSingleColumn()
    {
        $embeddedColumns = $this->relation->getMapper()->getDefinition()->getTable()->getColumns();

        return count($embeddedColumns) === 1 ? reset($embeddedColumns) : null;
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, string $tableAlias, string $operator, $value) : Expr
    {
        $isSingleColumn = $this->isSingleColumnObject();

        $multiColumnOperators = [
                ConditionOperator::EQUALS,
                ConditionOperator::NOT_EQUALS,
                ConditionOperator::IN,
                ConditionOperator::NOT_IN
        ];

        $isEqualityOperator = in_array($operator, $multiColumnOperators, true);

        if (!$isSingleColumn && !$isEqualityOperator) {
            throw MemberExpressionMappingException::format(
                    'Cannot compare value object of type %s using the \'%s\' operator, only (%s) are supported in multi-column value objects',
                    $this->getRelatedObjectType(), $operator, Debug::formatValues($multiColumnOperators)
            );
        }

        return $this->loadExpressionWithNecessarySubselects($select, $tableAlias, function (Select $select, $tableAlias) use ($operator, $value) {
            return $this->loadWhereConditionExpr($select, $tableAlias, $operator, $value);
        });
    }

    protected function loadWhereConditionExpr(Select $select, $tableAlias, $operator, $value)
    {
        if ($operator === ConditionOperator::IN || $operator === ConditionOperator::NOT_IN) {
            $equalsItemExpressions = [];

            foreach ($value as $item) {
                $equalsItemExpressions[] = $this->getValueObjectConditionExpr($select, $tableAlias, BinOp::EQUAL, $item);
            }

            $condition = Expr::compoundOr($equalsItemExpressions);

            return $operator === ConditionOperator::IN ? $condition : Expr::not($condition);
        }

        if ($operator === ConditionOperator::EQUALS || $operator === ConditionOperator::NOT_EQUALS) {
            return $this->getValueObjectConditionExpr($select, $tableAlias, $this->mapConditionOperator($operator), $value);
        }

        // Must be single column at this point
        return $this->getValueObjectConditionExpr($select, $tableAlias, $this->mapConditionOperator($operator), $value);
    }

    protected function getValueObjectConditionExpr(Select $select, $tableAlias, $dbOperator, $value)
    {
        if ($value === null) {
            return $this->compareValueObjectWithNull($select, $tableAlias, $dbOperator);
        }

        $table = $select->getTableFromAlias($tableAlias);

        /** @var EmbeddedParentObjectMapping $embeddedDefinition */
        $embeddedDefinition = $this->relation->getEmbeddedObjectMapper()->getMapping();
        $rowData            = $embeddedDefinition->persistObject(PersistenceContext::dummy(), $value);
        $columnExpressions  = [];

        foreach ($rowData->getColumnData() as $columnName => $value) {
            $column = $table->getColumn($columnName);

            $columnExpressions[] = new BinOp(
                    Expr::column($tableAlias, $column),
                    $dbOperator,
                    Expr::param(null, $value)
            );
        }

        return $dbOperator === BinOp::EQUAL
                ? Expr::compoundAnd($columnExpressions)
                : Expr::compoundOr($columnExpressions);
    }

    protected function compareValueObjectWithNull(Select $select, $tableAlias, $dbOperator)
    {
        if ($this->relation->getObjectIssetColumnName() !== null) {
            $issetColumn = Expr::tableColumn($select->getTableFromAlias($tableAlias), $this->relation->getObjectIssetColumnName());

            if ($this->relation->issetColumnIsWithinValueObject()) {
                return $dbOperator === BinOp::EQUAL
                        ? Expr::isNull($issetColumn)
                        : Expr::isNotNull($issetColumn);
            } else {
                return $dbOperator === BinOp::EQUAL
                        ? Expr::equal($issetColumn, Expr::param($issetColumn->getResultingType(), false))
                        : Expr::equal($issetColumn, Expr::param($issetColumn->getResultingType(), true));
            }
        }

        return Expr::false();
    }

    /**
     * @inheritDoc
     */
    public function addOrderByToSelect(Select $select, string $tableAlias, bool $isAsc)
    {
        if (!$this->isSingleColumnObject()) {
            throw InvalidOperationException::format(
                    'Cannot order by value object of type %s: multi-column value objects ordering are not supported',
                    $this->getRelatedObjectType()
            );
        }

        $columnExpr = Expr::tableColumn($select->getTableFromAlias($tableAlias), $this->getSingleColumn()->getName());

        $this->addOrderBy($select, $columnExpr, $isAsc);
    }

    /**
     * @inheritDoc
     */
    public function addSelectColumn(Select $select, string $tableAlias, string $alias)
    {
        throw InvalidOperationException::format('Cannot select an value object of type %s as a column', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : Expr
    {
        if (!$this->isSingleColumnObject()) {
            throw InvalidOperationException::format('Cannot treat a value object of type %s as an expression: value object is composed of multiple columns', $this->getRelatedObjectType());
        }

        return Expr::column($tableAlias, $this->getSingleColumn());
    }
}