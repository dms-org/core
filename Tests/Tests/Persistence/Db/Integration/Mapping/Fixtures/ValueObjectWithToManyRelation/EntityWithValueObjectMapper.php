<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithToManyRelation;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToManyIdRelation\ChildEntity;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithValueObjectMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                EntityWithValueObject::class => __CLASS__,
                ChildEntity::class           => ChildEntityMapper::class,
        ], [
                EmbeddedObject::class => EmbeddedObjectMapper::class
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
        $map->type(EntityWithValueObject::class);
        $map->toTable('entities');

        $map->idToPrimaryKey('id');

        $map->embedded('embedded')->to(EmbeddedObject::class);
    }
}