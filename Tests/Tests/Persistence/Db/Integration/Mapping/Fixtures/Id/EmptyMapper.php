<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmptyMapper extends EntityMapper
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
        $map->type(EmptyEntity::class);
        $map->toTable('data');

        $map->idToPrimaryKey('id');
    }
}