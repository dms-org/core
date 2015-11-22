<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;

/**
 * The relation mapping base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationMapping extends MemberMapping
{
    /**
     * @var IRelation
     */
    protected $relation;

    /**
     * RelationMapping constructor.
     *
     * @param IEntityMapper $rootEntityMapper
     * @param IRelation[]   $nestedRelations
     * @param IRelation     $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $nestedRelations, IRelation $relation)
    {
        parent::__construct($rootEntityMapper, array_merge($nestedRelations, [$relation]));
        $this->relation = $relation;
    }

    /**
     * @return IRelation
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @return IRelation
     */
    public function getFirstRelation()
    {
        return reset($this->nestedRelations) ?: $this->relation;
    }

    /**
     * @return string
     */
    protected function getRelatedObjectType()
    {
        return $this->relation->getMapper()->getObjectType();
    }
}