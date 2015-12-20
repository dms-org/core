<?php

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;

/**
 * The relation mapping interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IToManyRelationMapping
{
    /**
     * @return IAccessor
     */
    public function getAccessor();

    /**
     * @return IRelation
     */
    public function getRelation();
}