<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;

/**
 * The bool validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BoolValidator extends FieldValidator
{
    const MESSAGE = 'validation.bool';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (is_object($input)
            || is_array($input)
            || filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
            $messages[] = new Message(self::MESSAGE);
        }
    }
}