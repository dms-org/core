<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\File\IUploadedImage;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;

/**
 * The image validator.
 * This assumes it has already been validated as a file.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ImageValidator extends FieldValidator
{
    const MESSAGE = 'validation.image';

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!($input instanceof IUploadedImage)) {
            $messages[] = new Message(self::MESSAGE);
        }
    }
}