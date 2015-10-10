<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;

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
     */
    public function __construct(IAccessor $accessor, IToManyRelation $relation)
    {
        parent::__construct($accessor, $relation);
    }

    /**
     * @return IToManyRelation
     */
    public function getRelation()
    {
        return parent::getRelation();
    }
}