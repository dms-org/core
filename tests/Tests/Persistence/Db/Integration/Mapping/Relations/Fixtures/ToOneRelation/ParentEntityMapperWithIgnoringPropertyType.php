<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntityMapperWithIgnoringPropertyType extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
            ParentEntityWithMixedProperty::class => __CLASS__,
            SubEntity::class                     => SubEntityMapper::class,
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
        $map->type(ParentEntityWithMixedProperty::class);
        $map->toTable('parent_entities');

        $map->idToPrimaryKey('id');

        $map->relation('child')
            ->ignoreTypeMismatch()
            ->to(SubEntity::class)
            ->toOne()
            ->withParentIdAs('parent_id');
    }
}