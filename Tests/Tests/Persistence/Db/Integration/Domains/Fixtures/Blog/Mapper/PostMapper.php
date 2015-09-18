<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Post;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PostMapper extends EntityMapper
{
    /**
     * @var UserMapper
     */
    private $userMapper;

    /**
     * PostMapper constructor.
     *
     * @param UserMapper $userMapper
     */
    public function __construct(UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
        parent::__construct('posts');
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
        $map->type(Post::class);

        $map->idToPrimaryKey('id');
        $map->column('author_id')->asInt();

        $map->property('content')->to('content')->asText();

        $map->relation('authorId')
            ->using($this->userMapper)
            ->manyToOneId()
            ->withRelatedIdAs('author_id');

        $map->relation('comments')
            ->using(new CommentMapper($this->userMapper))
            ->toMany()
            ->identifying()
            ->withParentIdAs('post_id');
    }
}