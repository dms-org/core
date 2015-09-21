<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\PropertyTypes;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyTypesMapper extends EntityMapper
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
        $map->type(PropertyTypesEntity::class);
        $map->toTable('property_types');

        $map->idToPrimaryKey('id');

        $map->property('value')->to('value')->asVarchar(255);
        $map->method('getValueUpperCase')->to('value_upper')->asVarchar(255);
        $map->computed(function (PropertyTypesEntity $entity) {
            return strtolower($entity->value);
        })->to('value_lower')->asVarchar(255);
    }
}