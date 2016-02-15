<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;

/**
 * The not supplied validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NotSuppliedValidator extends FieldValidator
{
    const MESSAGE = 'validation.not-supplied';

    /**
     * @inheritDoc
     */
    public function process($input, array &$messages)
    {
        if ($input !== null) {
            $messages[] = new Message(self::MESSAGE);
        }

        return null;
    }


    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {

    }
}