<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\ReadModel\Relation;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneRelationMapping;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ParentChildMap;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;
use Dms\Core\Persistence\Db\PersistenceContext;

/**
 * The to-one member relation class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneMemberRelation extends MemberRelation implements IToOneRelation
{
    /**
     * @var ToOneRelationMapping
     */
    protected $memberMapping;

    /**
     * @inheritDoc
     */
    public function __construct(ToOneRelationMapping $memberMapping)
    {
        parent::__construct($memberMapping);
    }

    /**
     * @param LoadingContext $context
     * @param ParentChildMap $map
     *
     * @return void
     */
    public function load(LoadingContext $context, ParentChildMap $map)
    {
        $this->loadRelation($context, $map);
    }

    public function withReference(IToOneRelationReference $reference)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persist(PersistenceContext $context, ParentChildMap $map)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}