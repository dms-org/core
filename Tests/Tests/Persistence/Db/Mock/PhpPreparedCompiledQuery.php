<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mock;

use Iddigital\Cms\Core\Persistence\Db\Connection\Query;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PhpPreparedCompiledQuery extends Query
{
    /**
     * @var MockConnection
     */
    protected $connection;

    /**
     * @var callable
     */
    private $compiled;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var mixed
     */
    private $result;

    /**
     * @inheritDoc
     */
    public function __construct(callable $compiled)
    {
        $this->compiled = $compiled;
    }

    /**
     * @param MockConnection $connection
     *
     * @return void
     */
    public function setConnection(MockConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $parameter
     * @param mixed  $value
     *
     * @return void
     */
    protected function doSetParameter($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * @return void
     */
    protected function doExecute()
    {
        $this->result = call_user_func($this->compiled, $this->connection->getDb(), $this->parameters);
    }

    /**
     * @return int
     */
    protected function loadAffectedRows()
    {
        return $this->result;
    }

    /**
     * @return array[]
     */
    protected function loadResults()
    {
        return $this->result;
    }
}