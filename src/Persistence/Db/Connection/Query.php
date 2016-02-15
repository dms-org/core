<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Connection;

use Dms\Core\Exception\InvalidOperationException;

/**
 * The query base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Query implements IQuery
{
    /**
     * @var IConnection
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $executed = false;

    /**
     * @param IConnection $connection
     */
    public function __construct(IConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return IConnection
     */
    final public function getConnection() : IConnection
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     */
    final public function setParameter($parameter, $value) : IQuery
    {
        $this->doSetParameter($parameter, $value);

        return $this;
    }

    /**
     * @param int|string $parameter
     * @param mixed      $value
     *
     * @return void
     */
    abstract protected function doSetParameter($parameter, $value);

    /**
     * {@inheritDoc}
     */
    final public function setParameters(array $parameters) : IQuery
    {
        $this->doSetParameters($parameters);

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return void
     */
    protected function doSetParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
    }

    /**
     * @inheritDoc
     */
    final public function execute(array $parameters = []) : IQuery
    {
        $this->setParameters($parameters);
        $this->doExecute();

        $this->executed = true;

        return $this;
    }

    /**
     * @return void
     */
    abstract protected function doExecute();

    /**
     * @inheritDoc
     */
    final public function hasExecuted() : bool
    {
        return $this->executed;
    }

    /**
     * @inheritDoc
     */
    final public function getAffectedRows()
    {
        $this->verifyExecuted(__METHOD__);

        return $this->loadAffectedRows();
    }

    /**
     * @return int
     */
    abstract protected function loadAffectedRows() : int;


    /**
     * @inheritDoc
     */
    final public function getResults()
    {
        $this->verifyExecuted(__METHOD__);

        return $this->loadResults();
    }

    /**
     * @return array[]
     */
    abstract protected function loadResults() : array;

    protected function verifyExecuted($method)
    {
        if (!$this->executed) {
            throw InvalidOperationException::format(
                'Invalid call to %s::%s: query has not been executed, call %s::%s to run the query',
                get_class($this), $method, get_class($this), 'execute'
            );
        }
    }
}