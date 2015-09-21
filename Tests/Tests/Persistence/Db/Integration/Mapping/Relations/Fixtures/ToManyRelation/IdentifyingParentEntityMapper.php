<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IdentifyingParentEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                ParentEntity::class => __CLASS__,
                ChildEntity::class  => ChildEntityMapper::class
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
                ->identifying()
                ->withParentIdAs('parent_id');
    }
}