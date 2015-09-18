<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Language\Message;

/**
 * The min array length validator
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MinArrayLengthValidator extends ComparisonValidator
{
    const MESSAGE = 'validation.min-array-length';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (count($input) < $this->value) {
            $messages[] = new Message(self::MESSAGE, ['length' => $this->value]);
        }
    }
}