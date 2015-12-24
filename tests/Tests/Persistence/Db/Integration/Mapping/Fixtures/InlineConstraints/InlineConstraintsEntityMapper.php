<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\InlineConstraints;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InlineConstraintsEntityMapper extends EntityMapper
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

        $map->property('indexed')->to('indexed')->index('index_name')->asVarchar(255);
        $map->property('unique')->to('unique')->unique('unique_index_name')->asInt();

        $map->property('defaultIndexed')->to('default')->index()->asVarchar(255);
        $map->property('defaultUnique')->to('default2')->unique()->asInt();
    }
}