<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;

/**
 * The upsert query.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Upsert extends RowSetQuery
{
    /**
     * {@inheritdoc}
     */
    public function executeOn(IConnection $connection)
    {
        $connection->upsert($this);
    }
}