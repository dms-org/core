<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition;

/**
 * The embedded read model property name alias definer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedReadModelAliasDefiner
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * EmbeddedReadModelAliasDefiner constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function to($propertyName)
    {
        call_user_func($this->callback, $propertyName);
    }
}