<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\ValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildValueObjectMapper extends ValueObjectMapper
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
        $map->type(ChildValueObject::class);

        $map->relation('entity')
            ->to(ChildEntity::class)
            ->manyToOne()
            ->onDeleteCascade()
            ->withRelatedIdAs('child_id');

        $map->embeddedCollection('children')
            ->toTable('value_object_children')
            ->withPrimaryKey('id')
            ->withForeignKeyToParentAs('value_object_id')
            ->using(new ChildChildValueObjectMapper());

        $map->column('child_id')->asInt();
    }
}