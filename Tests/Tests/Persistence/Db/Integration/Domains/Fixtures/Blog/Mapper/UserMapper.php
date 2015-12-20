<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Alias;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Comment;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\HashedPassword;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Post;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\User;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UserMapper extends EntityMapper
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
        $map->type(User::class);
        $map->toTable('users');

        $map->idToPrimaryKey('id');

        $map->property('firstName')->to('first_name')->asVarchar(255);
        $map->property('lastName')->to('last_name')->asVarchar(255);
        $map->property('email')->to('email')->asVarchar(255);
        $map->property('dateOfBirth')->to('dob')->asDate();

        $map->enum('gender')->to('gender')->usingValuesFromConstants();
        $map->enum('status')->to('status')->usingValuesFromConstants();

        $map->embedded('password')
                ->withColumnsPrefixedBy('password_')
                ->to(HashedPassword::class);

        $map->relation('postIds')
                ->to(Post::class)
                ->toManyIds()
                ->withBidirectionalRelation('authorId')
                ->withParentIdAs('author_id');

        $map->relation('friendIds')
                ->using($this)
                ->toManyIds()
                ->throughJoinTable('user_friends')
                ->withParentIdAs('user_id')
                ->withRelatedIdAs('friend_id');

        $map->relation('commentIds')
                ->to(Comment::class)
                ->toManyIds()
                ->withParentIdAs('author_id');

        $map->relation('alias')
                ->to(Alias::class)
                ->toOne()
                ->identifying()
                ->withParentIdAs('user_id');
    }
}