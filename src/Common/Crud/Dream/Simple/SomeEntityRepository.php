<?php

namespace Dms\Core\Common\Crud\Dream\Simple;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\DbRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SomeEntityRepository extends DbRepository
{
    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection)
    {
        parent::__construct($connection, new SomeEntityMapper('table'));
    }

}