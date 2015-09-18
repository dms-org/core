<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;

/**
 * The int validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntValidator extends FieldValidator
{
    const MESSAGE = 'validation.int';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!is_int(filter_var($input, FILTER_VALIDATE_INT))) {
            $messages[] = new Message(self::MESSAGE);
        }
    }
}