<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Ordering;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestOrderedEntityMapper extends EntityMapper
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
        $map->type(TestOrderedEntity::class);
        $map->toTable('data');

        $map->idToPrimaryKey('id');

        $map->enum(TestOrderedEntity::GROUP)->to('group')->usingValuesFromConstants();

        $map->property(TestOrderedEntity::ORDER)->to('order_index')->asInt();

        $map->hook()->saveOrderIndexTo('order_index', 'group');
    }
}