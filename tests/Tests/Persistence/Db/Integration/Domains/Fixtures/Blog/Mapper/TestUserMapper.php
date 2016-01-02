<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestAlias;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestComment;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestHashedPassword;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestPost;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestUser;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestUserMapper extends EntityMapper
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
        $map->type(TestUser::class);
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
                ->to(TestHashedPassword::class);

        $map->relation('postIds')
                ->to(TestPost::class)
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
                ->to(TestComment::class)
                ->toManyIds()
                ->withParentIdAs('author_id');

        $map->relation('alias')
                ->to(TestAlias::class)
                ->toOne()
                ->identifying()
                ->withParentIdAs('user_id');
    }
}