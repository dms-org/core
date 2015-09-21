<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ToOneRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RecursiveEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([RecursiveEntity::class => __CLASS__]);
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
        $map->type(RecursiveEntity::class);
        $map->toTable('recursive_entities');

        $map->idToPrimaryKey('id');
        $map->column('parent_id')->nullable()->asInt();

        $map->relation('parent')
                ->to(RecursiveEntity::class)
                ->toOne()
                ->withParentIdAs('parent_id');
    }
}