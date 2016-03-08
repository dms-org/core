<?php

namespace Dms\Core\Tests\Persistence\Db\Mock;

use Dms\Core\Persistence\Db\Connection\Query;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockQuery extends Query
{
    /**
     * @var array
     */
    protected $results;

    /**
     * MockQuery constructor.
     *
     * @param array $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @param int|string $parameter
     * @param mixed      $value
     *
     * @return void
     */
    protected function doSetParameter($parameter, $value)
    {

    }

    /**
     * @return void
     */
    protected function doExecute()
    {

    }

    /**
     * @return int
     */
    protected function loadAffectedRows() : int
    {

    }

    /**
     * @return array[]
     */
    protected function loadResults() : array
    {
        return $this->results;
    }
}