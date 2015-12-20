<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Language\Message;

/**
 * The less than or equal validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LessThanOrEqualValidator extends ComparisonValidator
{
    const MESSAGE = 'validation.less-than-or-equal';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!($input <= $this->value)) {
            $messages[] = new Message(self::MESSAGE, ['value' => $this->value]);
        }
    }
}