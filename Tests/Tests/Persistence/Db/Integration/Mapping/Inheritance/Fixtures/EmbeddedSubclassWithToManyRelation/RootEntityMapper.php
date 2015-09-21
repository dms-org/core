<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\EmbeddedSubclassWithToManyRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RootEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                RootEntity::class  => __CLASS__,
                ChildEntity::class => ChildEntityMapper::class,
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
        $map->type(RootEntity::class);
        $map->toTable('entities');

        $map->idToPrimaryKey('id');

        $map->column('type')->nullable()->asEnum(['subclass']);

        $map->subclass()->withTypeInColumn('type', 'subclass')->define(function (MapperDefinition $map) {
            $map->type(EntitySubclass::class);

            $map->relation('children')
                    ->to(ChildEntity::class)
                    ->toMany()
                    ->identifying()
                    ->withParentIdAs('parent_id');
        });
    }
}