<?php

namespace Iddigital\Cms\Core\Form\Field\Builder;

use Iddigital\Cms\Core\Form\Field\Processor\DateTimeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Iddigital\Cms\Core\Form\Field\Type\DateTimeTypeBase;
use Iddigital\Cms\Core\Form\Field\Type\DateType;

/**
 * The date time field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateFieldBuilder extends FieldBuilderBase
{
    /**
     * @var DateTimeTypeBase
     */
    protected $type;

    /**
     * @param DateTimeTypeBase      $type
     * @param FieldBuilderBase|null $previous
     *
     * @return DateFieldBuilder
     */
    public static function construct(
            DateTimeTypeBase $type,
            FieldBuilderBase $previous = null
    ) {
        $self       = new self($previous);
        $self->type = $type;

        return $self;
    }

    /**
     * Validates the date time is greater than or equal to
     * the supplied date time
     *
     * @param \DateTime $min
     *
     * @return static
     */
    public function min(\DateTime $min)
    {
        $min = $this->processCopy($min);

        return $this
                ->attr(DateType::ATTR_MIN, $min)
                ->validate(new GreaterThanOrEqualValidator($this->getCurrentProcessedType(), $min));
    }

    /**
     * Validates the date time is greater than the supplied date time
     *
     * @param \DateTime $value
     *
     * @return static
     */
    public function greaterThan(\DateTime $value)
    {
        $value = $this->processCopy($value);

        return $this
                ->attr(DateType::ATTR_MIN, $this->copy($value)->add($this->type->getUnit()))
                ->validate(new GreaterThanValidator($this->getCurrentProcessedType(), $value));
    }

    /**
     * Validates the date time is less than or equal to
     * the supplied date time
     *
     * @param \DateTime $max
     *
     * @return static
     */
    public function max(\DateTime $max)
    {
        $max = $this->processCopy($max);

        return $this
                ->attr(DateType::ATTR_MAX, $max)
                ->validate(new LessThanOrEqualValidator($this->getCurrentProcessedType(), $max));
    }

    /**
     * Validates the date time is greater than the supplied date time
     *
     * @param \DateTime $value
     *
     * @return static
     */
    public function lessThan(\DateTime $value)
    {
        $value = $this->processCopy($value);

        return $this
                ->attr(DateType::ATTR_MAX, $this->copy($value)->sub($this->type->getUnit()))
                ->validate(new LessThanValidator($this->getCurrentProcessedType(), $value));
    }

    /**
     * @param \DateTime $value
     *
     * @return \DateTime
     */
    protected function processCopy(\DateTime $value)
    {
        $copy = $this->copy($value);
        DateTimeProcessor::zeroUnusedParts($this->type->getMode(), $copy);

        return $copy;
    }

    /**
     * @param \DateTime $value
     *
     * @return \DateTime
     */
    protected function copy(\DateTime $value)
    {
        return clone $value;
    }
}