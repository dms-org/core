Orm classes
===========

Orm classes contain the necessary object mappers to map a domain model to a relational database.

A simple example can be found in the test fixtures:

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Alias;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Comment;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\HashedPassword;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Post;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\User;

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
        $orm->entity(User::class)->from(UserMapper::class);
        $orm->entity(Alias::class)->from(AliasMapper::class);
        $orm->entity(Post::class)->from(PostMapper::class);
        $orm->entity(Comment::class)->from(CommentMapper::class);

        $orm->valueObject(HashedPassword::class)->from(PasswordMapper::class);
    }
}

$orm        = new BlogOrm();
$userMapper = $orm->getEntityMapper(User::class);
```