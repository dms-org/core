<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestComment;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestPost;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestUser;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestPostMapper extends EntityMapper
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
        $map->type(TestPost::class);
        $map->toTable('posts');

        $map->idToPrimaryKey('id');
        $map->column('author_id')->asInt();

        $map->property('content')->to('content')->asText();

        $map->relation('authorId')
                ->to(TestUser::class)
                ->manyToOneId()
                ->withBidirectionalRelation('postIds')
                ->withRelatedIdAs('author_id');

        $map->relation('comments')
                ->to(TestComment::class)
                ->toMany()
                ->identifying()
                ->withParentIdAs('post_id');
    }
}