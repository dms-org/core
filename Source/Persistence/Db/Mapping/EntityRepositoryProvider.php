<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Model\Criteria\IEntitySetProvider;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\DbRepository;

/**
 * The entity repository provider interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityRepositoryProvider implements IEntitySetProvider
{
    /**
     * @var IOrm
     */
    protected $orm;

    /**
     * @var IConnection
     */
    protected $connection;

    /**
     * EntityRepositoryProvider constructor.
     *
     * @param IOrm        $orm
     * @param IConnection $connection
     */
    public function __construct(IOrm $orm, IConnection $connection)
    {
        $this->orm        = $orm;
        $this->connection = $connection;
    }

    /**
     * @return IOrm
     */
    public function getOrm()
    {
        return $this->orm;
    }

    /**
     * @return IConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Loads the data source for the supplied entity type.
     *
     * @param string $entityType
     *
     * @return IEntitySet
     */
    public function loadDataSourceFor($entityType)
    {
        $mapper = $this->orm->getEntityMapper($entityType);

        return new DbRepository($this->connection, $mapper);
    }
}