<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Language\Message;

/**
 * The exact length validator
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ExactLengthValidator extends ComparisonValidator
{
    const MESSAGE = 'validation.exact-length';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (strlen($input) !== $this->value) {
            $messages[] = new Message(self::MESSAGE, ['length' => $this->value]);
        }
    }
}