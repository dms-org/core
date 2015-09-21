<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\DateTimeValueObject;

use Iddigital\Cms\Core\Persistence\Db\Mapper\DateTimeMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithDateTimeMapper extends EntityMapper
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
        $map->type(EntityWithDateTime::class);
        $map->toTable('entities');

        $map->idToPrimaryKey('id');

        $map->embedded('datetime')->using(new DateTimeMapper('datetime'));
    }
}