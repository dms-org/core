<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntityMapper extends EntityMapper
{

    public static function orm()
    {
        return CustomOrm::from([
            ParentEntity::class     => __CLASS__,
            ChildEntity::class      => ChildEntityMapper::class,
            ChildChildEntity::class => ChildChildEntityMapper::class,
        ], [
            ChildValueObject::class => ChildValueObjectMapper::class,
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
        $map->toTable('parents');

        $map->idToPrimaryKey('id');

        $map->embeddedCollection('valueObjects')
            ->toTable('value_objects')
            ->withPrimaryKey('id')
            ->withForeignKeyToParentAs('parent_id')
            ->to(ChildValueObject::class);
    }
}