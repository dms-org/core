<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Language\Message;

/**
 * The greater than validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GreaterThanValidator extends ComparisonValidator
{
    const MESSAGE = 'validation.greater-than';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!($input > $this->value)) {
            $messages[] = new Message(self::MESSAGE, ['value' => $this->value]);
        }
    }
}