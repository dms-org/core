<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Language\Message;

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
        if (strlen((string)$input) !== $this->value) {
            $messages[] = new Message(self::MESSAGE, ['length' => $this->value]);
        }
    }
}