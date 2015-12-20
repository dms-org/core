<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Constraints;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConstraintsEntityMapper extends EntityMapper
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
        $map->type(ConstrainedEntity::class);
        $map->toTable('constrained');

        $map->idToPrimaryKey('id');

        $map->property('indexed')->to('indexed')->asVarchar(255);
        $map->property('unique')->to('unique')->asInt();
        $map->property('fk')->to('fk')->asInt();

        $map->index('index_name')->on('indexed');
        $map->unique('unique_index_name')->on('unique');

        $map->foreignKey('fk_name')
            ->columns('fk')
            ->references('some_other_id')
            ->on('some_other_table')
            ->onDeleteCascade()
            ->onUpdateCascade();
    }
}