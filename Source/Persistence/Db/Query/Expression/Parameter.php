<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query\Expression;

use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The parameter class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Parameter extends Expr
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Parameter constructor.
     *
     * @param Type  $type
     * @param mixed $value
     */
    public function __construct(Type $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets an array of the expressions contained within this expression.
     *
     * @return Expr[]
     */
    public function getChildren()
    {
        return [];
    }

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType()
    {
        return $this->type;
    }
}