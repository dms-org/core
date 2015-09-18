<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;

/**
 * The db delete query class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Delete extends Query
{
    /**
     * {@inheritdoc}
     */
    public function executeOn(IConnection $connection)
    {
        $connection->delete($this);
    }
}