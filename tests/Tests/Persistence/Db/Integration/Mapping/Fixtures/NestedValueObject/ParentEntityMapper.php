<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntityMapper extends EntityMapper
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
        $map->type(ParentEntity::class);
        $map->toTable('parents');

        $map->idToPrimaryKey('id');

        $map->embedded('one')->withColumnsPrefixedBy('one_')->using(new LevelOneMapper());
    }
}