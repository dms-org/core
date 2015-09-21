<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\Ordered;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntityWithOrderIndexColumnMapper extends EntityMapper
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
        $map->type(ChildEntity::class);
        $map->toTable('child_entities');

        $map->idToPrimaryKey('id');
        $map->column('parent_id')->nullable()->asInt();
        $map->column('order_index')->asInt();

        $map->property('val')->to('val')->asInt();
    }
}