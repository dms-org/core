<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Language\Message;

/**
 * The min length validator
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MinLengthValidator extends ComparisonValidator
{
    const MESSAGE = 'validation.min-length';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (strlen($input) < $this->value) {
            $messages[] = new Message(self::MESSAGE, ['min_length' => $this->value]);
        }
    }
}