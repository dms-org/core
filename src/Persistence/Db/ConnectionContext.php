<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db;

use Dms\Core\Persistence\Db\Connection\IConnection;

/**
 * The connection context class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ConnectionContext
{
    /**
     * @var IConnection
     */
    protected $connection;

    /**
     * ConnectionContext constructor.
     *
     * @param IConnection $connection
     */
    public function __construct(IConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return IConnection
     */
    final public function getConnection() : Connection\IConnection
    {
        return $this->connection;
    }
}