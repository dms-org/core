<?php

namespace Iddigital\Cms\Core\Form\Field\Builder;

use Iddigital\Cms\Core\Form\Field\Processor\Validator\DecimalPointsValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Iddigital\Cms\Core\Form\Field\Type\FloatType;

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
        return $this
                ->attr(FloatType::ATTR_MIN, (double)$min)
                ->validate(new GreaterThanOrEqualValidator($this->getCurrentProcessedType(), (double)$min));
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
        return $this
                ->attr(FloatType::ATTR_MIN, $value + 1.0)
                ->validate(new GreaterThanValidator($this->getCurrentProcessedType(), (double)$value));
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
        return $this
                ->attr(FloatType::ATTR_MAX, (double)$max)
                ->validate(new LessThanOrEqualValidator($this->getCurrentProcessedType(), (double)$max));
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
        return $this
                ->attr(FloatType::ATTR_MAX, $value - 1.0)
                ->validate(new LessThanValidator($this->getCurrentProcessedType(), (double)$value));
    }

    /**
     * Validates the decimal has a minimum number of decimal points.
     *
     * @param int $decimalPoints
     *
     * @return static
     */
    public function minDecimalPoints($decimalPoints)
    {
        return $this
                ->attr(FloatType::ATTR_MIN_DECIMAL_POINTS, $decimalPoints)
                ->validate(new DecimalPointsValidator($this->getCurrentProcessedType(), $decimalPoints, null));
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
        return $this
                ->attr(FloatType::ATTR_MAX_DECIMAL_POINTS, $decimalPoints)
                ->validate(new DecimalPointsValidator($this->getCurrentProcessedType(), null, $decimalPoints));
    }
}