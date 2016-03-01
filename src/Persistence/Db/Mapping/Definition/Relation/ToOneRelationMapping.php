<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;

/**
 * The to-one relation mapping class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneRelationMapping extends RelationMapping
{
    /**
     * ToManyRelationMapping constructor.
     *
     * @param IAccessor      $accessor
     * @param IToOneRelation $relation
     */
    public function __construct(IAccessor $accessor, IToOneRelation $relation)
    {
        parent::__construct($accessor, $relation, true);
    }

    /**
     * @return IToOneRelation
     */
    public function getRelation() : IRelation
    {
        return parent::getRelation();
    }
}