<?php

namespace Iddigital\Cms\Core\Persistence\Db\Schema\Type;

/**
 * The db type base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Type
{
    /**
     * @var bool
     */
    private $nullable = false;

    /**
     * @return bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * Sets the column type as nullable.
     *
     * @return static
     */
    public function nullable()
    {
        $clone = clone $this;

        $clone->nullable = true;

        return $clone;
    }
}