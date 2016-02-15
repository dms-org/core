<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Model\Criteria\IEntitySetProvider;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\DbRepository;

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
    public function getOrm() : IOrm
    {
        return $this->orm;
    }

    /**
     * @return IConnection
     */
    public function getConnection() : \Dms\Core\Persistence\Db\Connection\IConnection
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
    public function loadDataSourceFor(string $entityType) : \Dms\Core\Model\IEntitySet
    {
        $mapper = $this->orm->getEntityMapper($entityType);

        return new DbRepository($this->connection, $mapper);
    }
}