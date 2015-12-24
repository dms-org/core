<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Type\IntType;

/**
 * The integer field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntFieldBuilder extends FieldBuilderBase
{
    /**
     * Validates the integer is greater than or equal to the supplied number
     *
     * @param int $min
     *
     * @return static
     */
    public function min($min)
    {
        return $this->attr(IntType::ATTR_MIN, $min);
    }

    /**
     * Validates the integer is greater than the supplied number
     *
     * @param int $value
     *
     * @return static
     */
    public function greaterThan($value)
    {
        return $this->attr(IntType::ATTR_GREATER_THAN, $value);
    }

    /**
     * Validates the integer is less than or equal to the supplied number
     *
     * @param int $max
     *
     * @return static
     */
    public function max($max)
    {
        return $this->attr(IntType::ATTR_MAX, $max);
    }

    /**
     * Validates the integer is greater than the supplied number
     *
     * @param int $value
     *
     * @return static
     */
    public function lessThan($value)
    {
        return $this->attr(IntType::ATTR_LESS_THAN, $value);
    }
}