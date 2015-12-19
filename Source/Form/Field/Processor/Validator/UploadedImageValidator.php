<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\File\IUploadedImage;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;

/**
 * The uploaded file validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UploadedImageValidator extends FieldValidator
{
    const MESSAGE = 'validation.invalid-image';

    /**
     * Validates the supplied input and adds an
     * error messages to the supplied array.
     *
     * @param mixed     $input
     * @param Message[] $messages
     *
     * @throws InvalidArgumentException
     */
    protected function validate($input, array &$messages)
    {
        /** @var IUploadedImage $input */

        if (!$input->isValidImage()) {
            $messages[] = new Message(self::MESSAGE);
        }
    }
}