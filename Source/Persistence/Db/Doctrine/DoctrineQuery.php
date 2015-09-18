<?php

namespace Iddigital\Cms\Core\Persistence\Db\Doctrine;

use Doctrine\DBAL\Driver\Statement;
use Iddigital\Cms\Core\Persistence\Db\Connection\Query;
use Iddigital\Cms\Core\Persistence\PersistenceException;

/**
 * The doctrine query
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrineQuery extends Query
{
    /**
     * @var DoctrineConnection
     */
    protected $connection;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrineConnection;

    /**
     * @var Statement
     */
    protected $statement;

    /**
     * @inheritDoc
     */
    public function __construct(DoctrineConnection $connection, Statement $statement, array $parameters = [])
    {
        parent::__construct($connection);
        $this->doctrineConnection = $connection->getDoctrineConnection();
        $this->statement          = $statement;
        $this->setParameters($parameters);
    }

    /**
     * @inheritDoc
     */
    protected function doSetParameter($parameter, $value)
    {
        $success = $this->statement->bindValue($parameter, $value);

        if (!$success) {
            throw PersistenceException::format('Could not bind parameter \'%s\'', $parameter);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function doExecute()
    {
        $success = $this->statement->execute();

        if (!$success) {
            throw PersistenceException::format('Query execution failed');
        }
    }

    /**
     * @inheritDoc
     */
    protected function loadAffectedRows()
    {
        return $this->statement->rowCount();
    }

    /**
     * @inheritDoc
     */
    protected function loadResults()
    {
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}