<?php

namespace Dms\Core\Persistence\Db\Schema;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Type\IType;
use Dms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The column class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Column
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var bool
     */
    private $isPrimaryKey;

    /**
     * Column constructor.
     *
     * @param string $name
     * @param Type  $type
     * @param bool   $isPrimaryKey
     */
    public function __construct($name, Type $type, $isPrimaryKey = false)
    {
        InvalidArgumentException::verify(is_string($name), 'Column name must be a string, %s given', gettype($name));

        $this->name         = $name;
        $this->type         = $type;
        $this->isPrimaryKey = $isPrimaryKey;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey()
    {
        return $this->isPrimaryKey;
    }

    /**
     * @param string $name
     *
     * @return Column
     */
    public function withName($name)
    {
        if ($this->name === $name) {
            return $this;
        }

        return new self($name, $this->type, $this->isPrimaryKey);
    }

    /**
     * @return Column
     */
    public function asNullable()
    {
        return new self($this->name, $this->type->nullable(), $this->isPrimaryKey);
    }

    /**
     * @param $prefix
     *
     * @return Column
     */
    public function withPrefix($prefix)
    {
        return $this->withName($prefix . $this->name);
    }
}