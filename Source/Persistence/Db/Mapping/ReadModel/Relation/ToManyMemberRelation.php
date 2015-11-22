<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping\ToManyRelationMapping;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;

/**
 * The to-many member relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyMemberRelation extends MemberRelation implements IToManyRelation
{
    /**
     * @var ToManyRelationMapping
     */
    protected $memberMapping;

    /**
     * @inheritDoc
     */
    public function __construct(ToManyRelationMapping $memberMapping)
    {
        parent::__construct($memberMapping);
    }

    /**
     * @inheritDoc
     */
    public function load(LoadingContext $context, ParentChildrenMap $map)
    {
        $this->loadRelation($context, $map);
    }

    /**
     * @inheritDoc
     */
    public function buildCollection(array $children)
    {
        return $this->memberMapping->getRelation()->buildCollection($children);
    }

    public function withReference(IToManyRelationReference $reference)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persist(PersistenceContext $context, ParentChildrenMap $map)
    {
        throw NotImplementedException::method(__METHOD__);
    }

}