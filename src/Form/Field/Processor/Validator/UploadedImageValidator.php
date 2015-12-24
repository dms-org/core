<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\File\IUploadedFile;
use Dms\Core\File\IUploadedImage;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;

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