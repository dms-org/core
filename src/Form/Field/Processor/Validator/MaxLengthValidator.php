<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Language\Message;

/**
 * The max length validator
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MaxLengthValidator extends ComparisonValidator
{
    const MESSAGE = 'validation.max-length';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (strlen($input) > $this->value) {
            $messages[] = new Message(self::MESSAGE, ['max_length' => $this->value]);
        }
    }
}