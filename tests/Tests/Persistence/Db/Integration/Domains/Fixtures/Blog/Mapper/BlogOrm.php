<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestAlias;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestComment;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestHashedPassword;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestPost;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestUser;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BlogOrm extends Orm
{
    /**
     * Defines the object mappers registered in the orm.
     *
     * @param OrmDefinition $orm
     *
     * @return void
     */
    protected function define(OrmDefinition $orm)
    {
        $orm->entity(TestUser::class)->from(TestUserMapper::class);
        $orm->entity(TestAlias::class)->from(TestAliasMapper::class);
        $orm->entity(TestPost::class)->from(TestPostMapper::class);
        $orm->entity(TestComment::class)->from(TestCommentMapper::class);

        $orm->valueObject(TestHashedPassword::class)->from(TestPasswordMapper::class);
    }
}