<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;

/**
 * The relation mapping interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IRelationMapping
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