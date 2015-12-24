<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Language\Message;

/**
 * The greater than or equal validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GreaterThanOrEqualValidator extends ComparisonValidator
{
    const MESSAGE = 'validation.greater-than-or-equal';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!($input >= $this->value)) {
            $messages[] = new Message(self::MESSAGE, ['value' => $this->value]);
        }
    }
}