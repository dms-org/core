<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\File\IUploadedImage;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;

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