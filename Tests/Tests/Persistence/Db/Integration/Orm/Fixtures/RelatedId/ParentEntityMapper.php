<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntityMapper extends EntityMapper
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
        $map->type(ParentEntity::class);
        $map->toTable('parents');

        $map->idToPrimaryKey('id');

        $map->relation('childIds')
                ->to(ChildEntity::class)
                ->toManyIds()
                ->identifying()
                ->withBidirectionalRelation('parentId')
                ->withParentIdAs('parent_id');
    }
}