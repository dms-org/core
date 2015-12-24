<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\Ordered;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ParentEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentWithChildOrderPersistenceColumnEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                ParentEntity::class => __CLASS__,
                ChildEntity::class  => ChildEntityWithOrderIndexColumnMapper::class
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
        $map->type(ParentEntity::class);
        $map->toTable('parent_entities');

        $map->idToPrimaryKey('id');

        $map->relation('children')
                ->to(ChildEntity::class)
                ->toMany()
                ->withOrderPersistedTo('order_index')
                ->withParentIdAs('parent_id');
    }
}