<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Doctrine\Fixtures;

use Doctrine\Tests\Mocks\ConnectionMock as DoctrineConnectionMock;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConnectionMock extends DoctrineConnectionMock
{
    public function __construct(array $params, $driver, $config = null, $eventManager = null)
    {
        parent::__construct($params, $driver, $config, $eventManager);
        $this->_platform = $driver->getDatabasePlatform();
    }


    /**
     * @override
     */
    public function getDatabasePlatform()
    {
        return $this->_platform;
    }
}