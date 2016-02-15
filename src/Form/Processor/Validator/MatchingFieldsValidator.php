<?php declare(strict_types = 1);

namespace Dms\Core\Form\Processor\Validator;

/**
 * The matching fields form validator.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class MatchingFieldsValidator extends FieldComparisonValidator
{
    const MESSAGE = 'validation.matching-fields';

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
        if (gettype($value1) !== gettype($value2)) {
            return false;
        }

        if (is_object($value1) || is_array($value1)) {
            return $value1 == $value2;
        }

        return $value1 === $value2;
    }
}
