<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\Polymorphic;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntityMapper extends EntityMapper
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
        $map->type(ChildEntity::class);
        $map->toTable('child_entities');

        $map->idToPrimaryKey('id');
        $map->column('parent_id')->nullable()->asInt();

        $map->property('val')->to('val')->asInt();

        $map->subclass()->asSeparateTable('child_subclasses')->define(function (MapperDefinition $map) {
            $map->type(ChildEntitySubclass::class);

            $map->primaryKey('id');
            $map->property('sub')->to('sub')->asVarchar(255);
        });
    }
}