<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;

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