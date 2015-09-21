<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithEnumMapper extends EntityMapper
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

        $map->enum('status')->to('status')->usingValuesFromConstants();

        $map->enum('nullableStatus')->to('nullable_status')->usingValuesFromConstants();

        $map->enum('gender')->to('gender')->usingValueMap([
                GenderEnum::MALE   => 'M',
                GenderEnum::FEMALE => 'F',
        ]);

        $map->enum('nullableGender')->to('nullable_gender')->usingValueMap([
                GenderEnum::MALE   => 'M',
                GenderEnum::FEMALE => 'F',
        ]);
    }
}