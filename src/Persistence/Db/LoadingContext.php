<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Query\Select;

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
    public function getIdentityMaps() : array
    {
        return $this->identityMaps;
    }

    /**
     * @param string $entityType
     *
     * @return IdentityMap
     */
    public function getIdentityMap(string $entityType) : IdentityMap
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
    public function query(Select $select) : RowSet
    {
        return $this->connection->load($select);
    }
}