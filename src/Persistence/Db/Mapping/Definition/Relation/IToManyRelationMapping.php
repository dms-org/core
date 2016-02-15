<?php declare(strict_types = 1);

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
    public function getAccessor() : IAccessor;

    /**
     * @return IRelation
     */
    public function getRelation() : \Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
}