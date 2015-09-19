<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Index;

/**
 * The index name definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IndexColumnsDefiner
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * IndexNameDefiner constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback         = $callback;
    }

    /**
     * Defines the index on the supplied column names.
     *
     * @param string|string[] $columnNames
     *
     * @return void
     */
    public function on($columnNames)
    {
        if (!is_array($columnNames)) {
            $columnNames = [(string)$columnNames];
        }

        call_user_func($this->callback, $columnNames);
    }
}