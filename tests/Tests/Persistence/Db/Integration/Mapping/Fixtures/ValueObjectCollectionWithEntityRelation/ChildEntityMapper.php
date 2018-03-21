<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntityMapper extends EntityMapper
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
        $map->toTable('children');

        $map->idToPrimaryKey('id');

        $map->relation('children')
            ->to(ChildChildEntity::class)
            ->toMany()
            ->identifying()
            ->withParentIdAs('child_id');
    }
}