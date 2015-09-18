<?php

namespace Iddigital\Cms\Core\Form\Field\Builder;

use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Iddigital\Cms\Core\Form\Field\Type\IntType;

/**
 * The integer field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntFieldBuilder extends FieldBuilderBase
{
    /**
     * Validates the integer is greater than or equal to
     * the supplied number
     *
     * @param int $min
     *
     * @return static
     */
    public function min($min)
    {
        return $this
                ->attr(IntType::ATTR_MIN, $min)
                ->validate(new GreaterThanOrEqualValidator($this->getCurrentProcessedType(), $min));
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
        return $this
                ->attr(IntType::ATTR_MIN, $value + 1)
                ->validate(new GreaterThanValidator($this->getCurrentProcessedType(), $value));
    }

    /**
     * Validates the integer is less than or equal to
     * the supplied number
     *
     * @param int $max
     *
     * @return static
     */
    public function max($max)
    {
        return $this
                ->attr(IntType::ATTR_MAX, $max)
                ->validate(new LessThanOrEqualValidator($this->getCurrentProcessedType(), $max));
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
        return $this
                ->attr(IntType::ATTR_MAX, $value - 1)
                ->validate(new LessThanValidator($this->getCurrentProcessedType(), $value));
    }
}