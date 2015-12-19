<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\FileSizeValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\UploadedFileValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UploadedFileValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new UploadedFileValidator(Type::object(IUploadedFile::class)->nullable());
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::object(IUploadedFile::class)->nullable();
    }


    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [null],
                [$this->mockUploadedFile(UPLOAD_ERR_OK)],
        ];
    }

    protected function mockUploadedFile($status)
    {
        $file = $this->getMockForAbstractClass(IUploadedFile::class);

        $file->expects($this->any())
                ->method('hasUploadedSuccessfully')
                ->willReturn($status === UPLOAD_ERR_OK);

        $file->expects($this->any())
                ->method('getUploadError')
                ->willReturn($status);

        return $file;
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [
                        $this->mockUploadedFile(UPLOAD_ERR_EXTENSION),
                        new Message(UploadedFileValidator::MESSAGE_INTERNAL)
                ],
                [
                        $this->mockUploadedFile(UPLOAD_ERR_CANT_WRITE),
                        new Message(UploadedFileValidator::MESSAGE_DISK_ERROR)
                ],
                [
                        $this->mockUploadedFile(UPLOAD_ERR_NO_TMP_DIR),
                        new Message(UploadedFileValidator::MESSAGE_NO_TEMP_DIR)
                ],
                [
                        $this->mockUploadedFile(UPLOAD_ERR_NO_FILE),
                        new Message(UploadedFileValidator::MESSAGE_NO_FILE)
                ],
                [
                        $this->mockUploadedFile(UPLOAD_ERR_PARTIAL),
                        new Message(UploadedFileValidator::MESSAGE_INCOMPLETE)
                ],
                [
                        $this->mockUploadedFile(UPLOAD_ERR_FORM_SIZE),
                        new Message(UploadedFileValidator::MESSAGE_TOO_LARGE)
                ],
                [
                        $this->mockUploadedFile(UPLOAD_ERR_INI_SIZE),
                        new Message(UploadedFileValidator::MESSAGE_TOO_LARGE)
                ],
        ];
    }

    public function testInvalidUploadStatus()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $actualMessages = [];
        $this->validator->process($this->mockUploadedFile('some-invalid-status'), $actualMessages);
    }
}