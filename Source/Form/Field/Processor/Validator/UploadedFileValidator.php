<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;

/**
 * The uploaded file validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UploadedFileValidator extends FieldValidator
{
    const MESSAGE_TOO_LARGE = 'validation.upload-error.max-size';
    const MESSAGE_NO_FILE = 'validation.upload-error.no-file';
    const MESSAGE_INCOMPLETE = 'validation.upload-error.incomplete';
    const MESSAGE_NO_TEMP_DIR = 'validation.upload-error.no-temp-dir';
    const MESSAGE_DISK_ERROR = 'validation.upload-error.disk-error';
    const MESSAGE_INTERNAL = 'validation.upload-error.internal';

    /**
     * @var array
     */
    private static $errorMap = [
            UPLOAD_ERR_INI_SIZE   => self::MESSAGE_TOO_LARGE,
            UPLOAD_ERR_FORM_SIZE  => self::MESSAGE_TOO_LARGE,
            UPLOAD_ERR_NO_FILE    => self::MESSAGE_NO_FILE,
            UPLOAD_ERR_PARTIAL    => self::MESSAGE_INCOMPLETE,
            UPLOAD_ERR_NO_TMP_DIR => self::MESSAGE_NO_TEMP_DIR,
            UPLOAD_ERR_CANT_WRITE => self::MESSAGE_DISK_ERROR,
            UPLOAD_ERR_EXTENSION  => self::MESSAGE_INTERNAL,
    ];

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
        /** @var IUploadedFile $input */

        if (!$input->hasUploadedSuccessfully()) {
            if (!isset(self::$errorMap[$input->getUploadError()])) {
                throw InvalidArgumentException::format('Unknown file upload error: \'%s\'', $input->getUploadError());
            }

            $messages[] = new Message(self::$errorMap[$input->getUploadError()]);
        }
    }
}