<?php

namespace Iddigital\Cms\Core\Persistence\Db;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;

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
     * LoadingContext constructor.
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
    final public function getConnection()
    {
        return $this->connection;
    }
}