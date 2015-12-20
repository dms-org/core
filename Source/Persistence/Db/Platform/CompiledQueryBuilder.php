<?php

namespace Dms\Core\Persistence\Db\Platform;

/**
 * The compiled query builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CompiledQueryBuilder
{
    /**
     * @var string
     */
    public $sql = '';

    /**
     * @var array
     */
    public $parameters = [];

    public function build()
    {
        return new CompiledQuery($this->sql, $this->parameters);
    }
}