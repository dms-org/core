<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine;

use Dms\Core\Persistence\Db\Connection\Connection;
use Dms\Core\Persistence\Db\Connection\IQuery;

/**
 * The doctrine connection
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrineConnection extends Connection
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrineConnection;

    /**
     * DoctrineConnection constructor.
     *
     * @param \Doctrine\DBAL\Connection $doctrineConnection
     */
    public function __construct(\Doctrine\DBAL\Connection $doctrineConnection)
    {
        parent::__construct(new DoctrinePlatform($doctrineConnection));
        $this->doctrineConnection = $doctrineConnection;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDoctrineConnection()
    {
        return $this->doctrineConnection;
    }

    /**
     * Gets the last insert id.
     *
     * @return int
     */
    public function getLastInsertId() : int
    {
        return (int)$this->doctrineConnection->lastInsertId();
    }

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->doctrineConnection->beginTransaction();
    }

    /**
     * Returns whether the connection is in a transaction.
     *
     * @return bool
     */
    public function isInTransaction() : bool
    {
        return $this->doctrineConnection->isTransactionActive();
    }

    /**
     * Commits the transaction.
     *
     * @return void
     */
    public function commitTransaction()
    {
        $this->doctrineConnection->commit();
    }

    /**
     * Rollsback the transaction.
     *
     * @return void
     */
    public function rollbackTransaction()
    {
        $this->doctrineConnection->rollBack();
    }

    /**
     * Creates a query with the specified sql and parameters.
     *
     * @param string $sql
     * @param array  $parameters
     *
     * @return IQuery
     */
    public function prepare($sql, array $parameters = []) : IQuery
    {
        return new DoctrineQuery($this, $this->doctrineConnection->prepare($sql), $parameters);
    }
}