<?php declare(strict_types = 1);

namespace Dms\Core\Form\Processor\Validator;

/**
 * The field greater than or equal another form validator.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class FieldGreaterThanOrEqualAnotherValidator extends FieldComparisonValidator
{
    const MESSAGE = 'validation.field-greater-than-or-equal-another';

    /**
     * @inheritDoc
     */
    protected function getMessageId() : string
    {
        return self::MESSAGE;
    }

    /**
     * @inheritDoc
     */
    protected function doValuesSatisfyComparison($value1, $value2) : bool
    {
        return $value1 >= $value2;
    }
}
