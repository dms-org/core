<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

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
    public function isNullable() : bool
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