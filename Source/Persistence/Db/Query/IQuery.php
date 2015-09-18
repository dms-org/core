<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;

/**
 * The db query interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IQuery
{
    /**
     * Executes the query on the supplied connection.
     *
     * @param IConnection $connection
     *
     * @return mixed
     */
    public function executeOn(IConnection $connection);
}