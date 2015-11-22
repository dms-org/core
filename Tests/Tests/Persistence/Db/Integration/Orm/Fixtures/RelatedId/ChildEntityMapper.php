<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntityMapper extends EntityMapper
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
        $map->type(ChildEntity::class);
        $map->toTable('children');

        $map->idToPrimaryKey('id');

        $map->column('parent_id')->asInt();

        $map->relation('parentId')
                ->to(ParentEntity::class)
                ->manyToOneId()
                ->onDeleteCascade()
                ->withBidirectionalRelation('childIds')
                ->withRelatedIdAs('parent_id');

        $map->property('data')->to('data')->nullable()->asVarchar(255);
    }
}