<?php

namespace Dms\Core\Tests\Persistence\Db\Mock;

use Dms\Core\Persistence\Db\Platform\CompiledQuery;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PhpCompiledQuery extends CompiledQuery
{
    /**
     * @var callable
     */
    private $compiled;

    /**
     * @inheritDoc
     */
    public function __construct(callable $compiled)
    {
        parent::__construct('', []);
        $this->compiled = $compiled;
    }

    /**
     * @return callable
     */
    public function getCompiled()
    {
        return $this->compiled;
    }

    public function executeOn(MockDatabase $database)
    {
        return call_user_func($this->compiled, $database, $this->getParameters());
    }
}