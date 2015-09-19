<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Doctrine\Mocks;

class ConnectionMock extends \Doctrine\DBAL\Connection
{
    private $_fetchOneResult;
    private $_lastInsertId = 0;
    private $_inserts = array();

    public function __construct(array $params, $driver, $config = null, $eventManager = null)
    {
        parent::__construct($params, $driver, $config, $eventManager);
        $this->_platform = $driver->getDatabasePlatform();
    }

    /**
     * @override
     */
    public function insert($tableName, array $data, array $types = array())
    {
        $this->_inserts[$tableName][] = $data;
    }

    /**
     * @override
     */
    public function lastInsertId($seqName = null)
    {
        return $this->_lastInsertId;
    }

    /**
     * @override
     */
    public function fetchColumn($statement, array $params = array(), $colnum = 0, array $types = array())
    {
        return $this->_fetchOneResult;
    }

    /**
     * @override
     */
    public function quote($input, $type = null)
    {
        if (is_string($input)) {
            return "'" . $input . "'";
        }
        return $input;
    }

    /* Mock API */

    public function setFetchOneResult($fetchOneResult)
    {
        $this->_fetchOneResult = $fetchOneResult;
    }

    public function setLastInsertId($id)
    {
        $this->_lastInsertId = $id;
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function reset()
    {
        $this->_inserts = array();
        $this->_lastInsertId = 0;
    }
}