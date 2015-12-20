<?php

namespace Dms\Core\Common\Crud\Dream\Simple;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SomeEntityMapper extends EntityMapper
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
        $map->type(SomeEntity::class);

        $map->idToPrimaryKey('id');

        $map->property('data')->to('data')->asText();
    }
}