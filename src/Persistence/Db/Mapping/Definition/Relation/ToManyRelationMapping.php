<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;

/**
 * The to-many relation mapping class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationMapping extends RelationMapping
{
    /**
     * ToManyRelationMapping constructor.
     *
     * @param IAccessor       $accessor
     * @param IToManyRelation $relation
     * @param bool            $ignoreTypeMismatch
     */
    public function __construct(IAccessor $accessor, IToManyRelation $relation, bool $ignoreTypeMismatch)
    {
        parent::__construct($accessor, $relation, false, $ignoreTypeMismatch);
    }

    /**
     * @return IToManyRelation
     */
    public function getRelation() : \Dms\Core\Persistence\Db\Mapping\Relation\IRelation
    {
        return parent::getRelation();
    }
}