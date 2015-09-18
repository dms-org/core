<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\EmbeddedSubclassWithToManyRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RootEntityMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('entities');
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

        $map->idToPrimaryKey('id');

        $map->column('type')->nullable()->asEnum(['subclass']);

        $map->subclass()->withTypeInColumn('type', 'subclass')->define(function (MapperDefinition $map) {
            $map->type(EntitySubclass::class);

            $map->relation('children')
                    ->using(new ChildEntityMapper())
                    ->toMany()
                    ->identifying()
                    ->withParentIdAs('parent_id');
        });
    }
}