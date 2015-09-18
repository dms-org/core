<?php

namespace Iddigital\Cms\Core\Persistence\Db;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The persistence context class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadingContext
{
    /**
     * @var IdentityMap[]
     */
    private $identityMaps = [];

    /**
     * @var IConnection
     */
    private $connection;

    /**
     * LoadingContext constructor.
     *
     * @param IConnection $connection
     */
    public function __construct(IConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return IdentityMap[]
     */
    public function getIdentityMaps()
    {
        return $this->identityMaps;
    }

    /**
     * @param string $entityType
     *
     * @return IdentityMap
     */
    public function getIdentityMap($entityType)
    {
        if (!isset($this->identityMaps[$entityType])) {
            $this->identityMaps[$entityType] = new IdentityMap($entityType);
        }

        return $this->identityMaps[$entityType];
    }

    /**
     * @param Select $select
     *
     * @return RowSet
     */
    public function query(Select $select)
    {
        return $this->connection->load($select);
    }
}