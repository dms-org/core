<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildChildEntityMapper extends EntityMapper
{
    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(ChildChildEntity::class);
        $map->toTable('children_children');

        $map->idToPrimaryKey('id');
        $map->property('data')->to('data')->asVarchar(255);

        $map->column('child_id')->asInt();
    }
}