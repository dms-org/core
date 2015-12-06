<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;

/**
 * The to-one relation mapping base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ToOneRelationMapping extends RelationMapping
{
    /**
     * @var IToOneRelation
     */
    protected $relation;

    /**
     * ToOneRelationMapping constructor.
     *
     * @param IEntityMapper  $rootEntityMapper
     * @param IRelation[]    $relationsToSubSelect
     * @param IToOneRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect, IToOneRelation $relation)
    {
        parent::__construct($rootEntityMapper, $relationsToSubSelect, $relation);
    }

    /**
     * @return IToOneRelation
     */
    public function getRelation()
    {
        return $this->relation;
    }
}