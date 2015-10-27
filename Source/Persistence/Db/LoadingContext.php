<?php

namespace Iddigital\Cms\Core\Persistence\Db;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The loading context class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadingContext extends ConnectionContext
{
    /**
     * @var IdentityMap[]
     */
    private $identityMaps = [];

    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection)
    {
        parent::__construct($connection);
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