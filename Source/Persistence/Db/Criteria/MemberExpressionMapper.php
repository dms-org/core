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
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
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
 * The member expression mapper class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberExpressionMapper
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
     * Maps the supplied member expression to an expression.
     *
     * @param NestedMember $member
     *
     * @return Expr
     * @throws InvalidArgumentException
     */
    public function mapMemberExpressionToSql(NestedMember $member)
    {

    }
}