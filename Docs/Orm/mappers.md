Object Mappers
==============

Mapper classes can be defined for both value objects and entities. They contain the configuration
of how to map an instance of a particular class to and from a database table.

## Entity Mappers

Entity mappers define the structure of a table of which to map the entity to.

When relationships are defined, the *parent* entity refers to the current entity which
is being mapped while the *child*/*related* entity refers to the one which the relationship
refers to.

Here is an example containing various property and relationship mapping configuration:

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Alias;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Comment;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\HashedPassword;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Post;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\User;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\UserStatus;

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
        // Defines the mapped class and the table which it is mapped to
        $map->type(User::class);
        $map->toTable('users');

        // Maps the 'id' property to an integer column named 'id'
        $map->idToPrimaryKey('id');

        // Maps single-value properties to individual columns
        $map->property('firstName')->to('first_name')->asVarchar(255);
        $map->property('lastName')->to('last_name')->asVarchar(255);
        $map->property('email')->to('email')->asVarchar(255);
        $map->property('dateOfBirth')->to('dob')->asDate();

        // Maps enums to columns using their constants as the values
        $map->enum('gender')->to('gender')->usingValuesFromConstants();

        // Maps enums to columns using a custom value map to store in the db
        $map->enum('status')->to('status')->usingValueMap([
            UserStatus::ACTIVE   => 'awesome',
            UserStatus::INACTIVE => 'crappy',
        ]);

        // Mapping value objects as embedded columns all prefixed by 'password_'
        // The value object mapper is resolved through the Orm instance
        $map->embedded('password')
                ->withColumnsPrefixedBy('password_')
                ->to(HashedPassword::class);

        // One-to-many (id reference):
        // Maps the property to an EntityIdCollection, contains a list of integer ids of the
        // related Post entities via a foreign key
        $map->relation('postIds')
                ->to(Post::class)
                ->toManyIds()
                ->withBidirectionalRelation('authorId')
                ->withParentIdAs('author_id');

        // Many-to-many (id reference) [self-referencing]:
        // Maps the property to an EntityIdCollection, contains a list of integer ids of the
        // related User entities via a many-to-many join table: 'user_friends'
        $map->relation('friendIds')
                ->using($this)
                ->toManyIds()
                ->throughJoinTable('user_friends')
                ->withParentIdAs('user_id')
                ->withRelatedIdAs('friend_id');

        // One-to-one (object reference):
        // Maps the property to the related instance of Alias via a foreign key
        $map->relation('alias')
                ->to(Alias::class)
                ->toOne()
                ->identifying()
                ->withParentIdAs('user_id');
    }
}
```

## Value Object Mappers

Value object mappers define the structure of value objects. They can be reused/embedded in
other entity mappers.

A simple example:

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\ValueObjectMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\HashedPassword;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PasswordMapper extends ValueObjectMapper
{
    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(HashedPassword::class);

        $map->property('hash')->to('hash')->asVarchar(255);
        $map->property('algorithm')->to('algorithm')->asVarchar(10);
    }
}
```