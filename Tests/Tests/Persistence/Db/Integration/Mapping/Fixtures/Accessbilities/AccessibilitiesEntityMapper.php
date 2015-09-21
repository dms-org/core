<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Accessbilities;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AccessibilitiesEntityMapper extends EntityMapper
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
        $map->type(AccessibilitiesEntity::class);
        $map->toTable('accessibilities');

        $map->idToPrimaryKey('id');

        $map->property('private')->to('private')->asInt();

        $map->property('protected')->to('protected')->asInt();

        $map->property('public')->to('public')->asInt();
    }
}