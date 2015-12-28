<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Type\FloatType;

/**
 * The decimal field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DecimalFieldBuilder extends FieldBuilderBase
{
    /**
     * Validates the decimal is greater than or equal to
     * the supplied number
     *
     * @param double $min
     *
     * @return static
     */
    public function min($min)
    {
        return $this->attr(FloatType::ATTR_MIN, (double)$min);
    }

    /**
     * Validates the decimal is greater than the supplied number
     *
     * @param double $value
     *
     * @return static
     */
    public function greaterThan($value)
    {
        return $this->attr(FloatType::ATTR_GREATER_THAN, (double)$value);
    }

    /**
     * Validates the decimal is less than or equal to
     * the supplied number
     *
     * @param double $max
     *
     * @return static
     */
    public function max($max)
    {
        return $this->attr(FloatType::ATTR_MAX, (double)$max);
    }

    /**
     * Validates the decimal is greater than the supplied number
     *
     * @param double $value
     *
     * @return static
     */
    public function lessThan($value)
    {
        return $this->attr(FloatType::ATTR_LESS_THAN, (double)$value);
    }

    /**
     * Validates the decimal has a maximum number of decimal points.
     *
     * @param int $decimalPoints
     *
     * @return static
     */
    public function maxDecimalPoints($decimalPoints)
    {
        return $this->attr(FloatType::ATTR_MAX_DECIMAL_POINTS, $decimalPoints);
    }
}