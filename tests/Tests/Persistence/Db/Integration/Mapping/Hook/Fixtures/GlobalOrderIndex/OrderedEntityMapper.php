<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GlobalOrderIndex;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderedEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                OrderedEntity::class => __CLASS__,
        ]);
    }

    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(OrderedEntity::class);
        $map->toTable('data');

        $map->idToPrimaryKey('id');

        $map->property('orderIndex')->ignoreNullabilityTypeMismatch()->to('order_index')->asInt();

        $map->hook()->saveOrderIndexTo('order_index');
    }
}