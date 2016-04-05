<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\CustomLoader;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomLoadedEntityMapper extends EntityMapper
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
        $map->type(CustomLoadedEntity::class);
        $map->toTable('entities');

        $map->idToPrimaryKey('id');

        $map->custom(function (array $entities) {
            foreach ($entities as $entity) {
                $entity->integer = 999;
            }
        });
        
        $map->ignoreUnmappedProperties();
    }
}