<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyRelation\Polymorphic;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyRelation\ChildEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntityMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('child_entities');
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
        $map->type(ChildEntity::class);

        $map->idToPrimaryKey('id');
        $map->column('parent_id')->nullable()->asInt();

        $map->property('val')->to('val')->asInt();

        $map->subclass()->asSeparateTable('child_subclasses')->define(function (MapperDefinition $map) {
            $map->type(ChildEntitySubclass::class);

            $map->primaryKey('id');
            $map->property('sub')->to('sub')->asVarchar(255);
        });
    }
}