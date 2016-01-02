<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestComment;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestUser;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestCommentMapper extends EntityMapper
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
        $map->type(TestComment::class);
        $map->toTable('comments');

        $map->idToPrimaryKey('id');
        $map->column('post_id')->asInt();
        $map->column('author_id')->nullable()->asInt();

        $map->property('content')->to('content')->asText();
        $map->relation('authorId')
                ->to(TestUser::class)
                ->manyToOneId()
                ->withBidirectionalRelation('commentIds')
                ->withRelatedIdAs('author_id');
    }
}