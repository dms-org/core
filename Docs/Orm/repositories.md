Repositories
============

[The repository interface][repo-interface] contains the api for storing and retrieving a set of
entities from an external data-store such as a relational database. The orm already provides an
implementation of the repository interface under [`Dms\Core\Persistence\DbRepository`][repo-db].

This class requires a db connection in the form of `Dms\Core\Persistence\Db\Connection\IConnection`
and an entity mapper to map the rows into object instances.

Custom repository specific methods can be added as required.

A simple repository example:

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\IRepository;
use Dms\Core\Persistence\DbRepository;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

class User extends Entity
{
    // ...
}

class UserMapper extends EntityMapper
{
    // ...
}

interface IUserRepository extends IRepository
{
    /**
     * @param string $userName
     *
     * @return User
     * @throws EntityNotFoundException
     */
    public function getUserByUserName($userName);

    // NOTE:
    // We can override the common methods to provide more specific @return
    // annotations to get better ide-autocompletion

    /**
     * {@inheritDoc}
     * @return User[]
     */
    public function getAll();

    /**
     * {@inheritDoc}
     * @return User
     */
    public function get($id);

    /**
     * {@inheritDoc}
     * @return User|null
     */
    public function tryGet($id);

    /**
     * {@inheritDoc}
     * @return User[]
     */
    public function tryGetAll(array $ids);

    /**
     * {@inheritDoc}
     * @return User[]
     */
    public function matching(ICriteria $criteria);

    /**
     * {@inheritDoc}
     * @return User[]
     */
    public function satisfying(ISpecification $specification);
}

class UserRepository extends DbRepository implements IUserRepository
{
    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection, UserMapper $userMapper)
    {
        parent::__construct($connection, $userMapper);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserByUserName($userName)
    {
        $results = $this->matching(
            $this->criteria()->where('userName', '=', $userName)
        );

        if (!$results) {
            throw new EntityNotFoundException(User::class, $userName, 'userName');
        }

        return $results[0];
    }
}
```

[repo-interface]: /Source/Persistence/IRepository.php
[repo-db]: /Source/Persistence/DbRepository.php