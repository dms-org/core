<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Accessors;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithAccessorMapper extends EntityMapper
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
        $map->type(EntityWithAccessor::class);
        $map->toTable('entities');

        $map->idToPrimaryKey('id');

        $map->accessor(
                function (EntityWithAccessor $entity) {
                    return $entity->getValue();
                },
                function (EntityWithAccessor $entity, $value) {
                    $entity->setValue($value);
                }
        )->to('value')->asVarchar(255);
    }
}