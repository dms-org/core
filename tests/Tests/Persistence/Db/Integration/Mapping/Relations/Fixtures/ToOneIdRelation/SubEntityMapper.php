<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubEntityMapper extends EntityMapper
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
        $map->type(SubEntity::class);
        $map->toTable('sub_entities');

        $map->idToPrimaryKey('id');
        $map->column('parent_id')->nullable()->asInt();

        $map->property('val')->to('val')->asInt();

    }
}