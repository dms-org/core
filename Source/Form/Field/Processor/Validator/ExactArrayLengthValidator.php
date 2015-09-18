<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Language\Message;

/**
 * The exact array length validator
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ExactArrayLengthValidator extends ComparisonValidator
{
    const MESSAGE = 'validation.exact-array-length';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (count($input) !== $this->value) {
            $messages[] = new Message(self::MESSAGE, ['length' => $this->value]);
        }
    }
}