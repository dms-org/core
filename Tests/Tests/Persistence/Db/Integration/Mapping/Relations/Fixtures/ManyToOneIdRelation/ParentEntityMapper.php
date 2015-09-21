<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToOneIdRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                ParentEntity::class => __CLASS__,
                SubEntity::class    => SubEntityMapper::class,
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
        $map->column('child_id')->nullable()->asInt();

        $map->relation('childId')
                ->to(SubEntity::class)
                ->manyToOneId()
                ->withRelatedIdAs('child_id');
    }
}