<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;

/**
 * The float validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FloatValidator extends FieldValidator
{
    const MESSAGE = 'validation.float';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!is_float(filter_var($input, FILTER_VALIDATE_FLOAT))) {
            $messages[] = new Message(self::MESSAGE);
        }
    }
}