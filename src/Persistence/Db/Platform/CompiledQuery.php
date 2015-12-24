<?php

namespace Dms\Core\Persistence\Db\Platform;

/**
 * The compiled query class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CompiledQuery
{
    /**
     * @var string
     */
    private $sql;

    /**
     * @var array
     */
    private $parameters;

    /**
     * CompiledQuery constructor.
     *
     * @param string $sql
     * @param array  $parameters
     */
    public function __construct($sql, array $parameters)
    {
        $this->sql        = $sql;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}