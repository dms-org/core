<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithEnumAsVarcharsMapper extends EntityMapper
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
        $map->type(EntityWithEnum::class);
        $map->toTable('data');

        $map->idToPrimaryKey('id');

        $map->enum('status')->to('status')->asVarchar(255);

        $map->enum('nullableStatus')->to('nullable_status')->asVarchar(255);

        $map->enum('gender')->to('gender')->asVarchar(1);

        $map->enum('nullableGender')->to('nullable_gender')->asVarchar(1);
    }
}