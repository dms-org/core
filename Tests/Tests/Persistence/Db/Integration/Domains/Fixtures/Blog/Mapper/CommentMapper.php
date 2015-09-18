<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Comment;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CommentMapper extends EntityMapper
{
    /**
     * @var UserMapper
     */
    private $userMapper;

    /**
     * @inheritDoc
     */
    public function __construct(UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
        parent::__construct('comments');
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
        $map->type(Comment::class);

        $map->idToPrimaryKey('id');
        $map->column('post_id')->asInt();
        $map->column('author_id')->asInt();

        $map->property('content')->to('content')->asText();
        $map->relation('authorId')
            ->using($this->userMapper)
            ->manyToOneId()
            ->withRelatedIdAs('author_id');
    }
}