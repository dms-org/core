<?php

namespace Iddigital\Cms\Core\Persistence\Db\Connection;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Platform\IPlatform;

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
     * @return IPlatform
     */
    final public function getConnection()
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     */
    final public function setParameter($parameter, $value)
    {
        $this->doSetParameter($parameter, $value);

        return $this;
    }

    /**
     * @param string $parameter
     * @param mixed $value
     *
     * @return void
     */
    abstract protected function doSetParameter($parameter, $value);

    /**
     * {@inheritDoc}
     */
    final public function setParameters(array $parameters)
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
    final public function execute(array $parameters = [])
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
    final public function hasExecuted()
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
    abstract protected function loadAffectedRows();


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
    abstract protected function loadResults();

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