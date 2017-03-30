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
     * @param bool           $ignoreTypeMismatch
     */
    public function __construct(IAccessor $accessor, IToOneRelation $relation, bool $ignoreTypeMismatch)
    {
        parent::__construct($accessor, $relation, true, $ignoreTypeMismatch);
    }

    /**
     * @return IToOneRelation
     */
    public function getRelation() : IRelation
    {
        return parent::getRelation();
    }
}