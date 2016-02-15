<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;

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