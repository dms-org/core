<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Language\Message;

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