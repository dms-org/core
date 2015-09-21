<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\Polymorphic;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                ParentEntity::class => __CLASS__,
                SubEntity::class  => SubEntityMapper::class
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

        $map->relation('child')
                ->to(SubEntity::class)
                ->toOne()
                ->identifying()
                ->withParentIdAs('parent_id');
    }
}