<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Processor\DateTimeProcessor;
use Dms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\GreaterThanValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Dms\Core\Form\Field\Type\DateTimeTypeBase;
use Dms\Core\Form\Field\Type\DateType;

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
     * @param \DateTimeInterface $min
     *
     * @return static
     */
    public function min(\DateTimeInterface $min)
    {
        $min = $this->processDateTime($min);

        return $this
                ->attr(DateTimeTypeBase::ATTR_MIN, $min)
                ->validate(new GreaterThanOrEqualValidator($this->getCurrentProcessedType(), $min));
    }

    /**
     * Validates the date time is greater than the supplied date time
     *
     * @param \DateTimeInterface $value
     *
     * @return static
     */
    public function greaterThan(\DateTimeInterface $value)
    {
        $value = $this->processDateTime($value);

        return $this
                ->attr(DateTimeTypeBase::ATTR_MIN, $value->add($this->type->getUnit()))
                ->validate(new GreaterThanValidator($this->getCurrentProcessedType(), $value));
    }

    /**
     * Validates the date time is less than or equal to
     * the supplied date time
     *
     * @param \DateTimeInterface $max
     *
     * @return static
     */
    public function max(\DateTimeInterface $max)
    {
        $max = $this->processDateTime($max);

        return $this
                ->attr(DateTimeTypeBase::ATTR_MAX, $max)
                ->validate(new LessThanOrEqualValidator($this->getCurrentProcessedType(), $max));
    }

    /**
     * Validates the date time is greater than the supplied date time
     *
     * @param \DateTimeInterface $value
     *
     * @return static
     */
    public function lessThan(\DateTimeInterface $value)
    {
        $value = $this->processDateTime($value);

        return $this
                ->attr(DateTimeTypeBase::ATTR_MAX, $value->sub($this->type->getUnit()))
                ->validate(new LessThanValidator($this->getCurrentProcessedType(), $value));
    }

    /**
     * @param \DateTimeInterface $value
     *
     * @return \DateTimeImmutable
     */
    protected function processDateTime(\DateTimeInterface $value)
    {
        $newDateTime = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $value->format('Y-m-d H:i:s'),
                $value->getTimezone()
        );

        return DateTimeProcessor::zeroUnusedParts($this->type->getMode(), $newDateTime);
    }

    /**
     * @inheritDoc
     */
    protected function processDefaultValue($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $this->processDateTime($value);
        } else {
            return parent::processDefaultValue($value);
        }
    }
}