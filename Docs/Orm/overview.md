Overview
========

#### `Dms\Core\Persistence\Db\*`

The core cms package comes with a dedicated ORM component to aide in the development of creating
domain models which can be normalized in an underlying relational database.

The goals of this ORM component is to balance easy of use, flexibility, speed of development while
providing a decoupled domain model hence this is a 100% data mapper orm, the antithesis of the
active record orms. This ORM focuses on being declarative, you tell it what to do, not how to do it
and is configuration over convention to maximum flexibility where required.

To use this orm, you must create *mapper* classes. For instance if you defined a `Person` entity
in your domain model, you would create a `PersonMapper` class in your persistence layer which is
configured to store the `Person` class in a database table.

After implementing the required mapper classes, you define all them in an orm class extending
the `Dms\Core\Persistence\Db\Mapping\Orm` class which will act as a factory setting
up all the relationships where necessary.

Some complete and tested examples can be found in the [test fixtures][orm-fixtures].

[orm-fixtures]: /Tests/Tests/Persistence/Db/Integration/Domains/Fixtures/