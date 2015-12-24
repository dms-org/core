<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\Polymorphic;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\AnotherEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AnotherEntityMapper extends EntityMapper
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
        $map->type(AnotherEntity::class);
        $map->toTable('anothers');

        $map->idToPrimaryKey('id');

        $map->property('val')->to('val')->asInt();

        $map->subclass()->asSeparateTable('another_subclasses')->define(function (MapperDefinition $map) {
            $map->type(AnotherEntitySubclass::class);

            $map->primaryKey('id');
            $map->property('data')->to('data')->asBool();
        });
    }
}