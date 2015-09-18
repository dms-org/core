<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\FileSizeValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileSizeValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new FileSizeValidator(Type::object(IUploadedFile::class)->nullable(), 100, 500);
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
            [$this->mockUploadedFile(100)],
            [$this->mockUploadedFile(500)],
            [$this->mockUploadedFile(250)],
            [$this->mockUploadedFile(101)],
            [$this->mockUploadedFile(499)],
        ];
    }

    protected function mockUploadedFile($size)
    {
        $file = $this->getMockForAbstractClass(IUploadedFile::class);

        $file->expects($this->any())
            ->method('getSize')
            ->willReturn($size);

        return $file;
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [
                $this->mockUploadedFile(20),
                new Message(FileSizeValidator::MESSAGE_MIN, ['min_size' => 100])
            ],
            [
                $this->mockUploadedFile(99),
                new Message(FileSizeValidator::MESSAGE_MIN, ['min_size' => 100])
            ],
            [
                $this->mockUploadedFile(501),
                new Message(FileSizeValidator::MESSAGE_MAX, ['max_size' => 500])
            ],
            [
                $this->mockUploadedFile(25000),
                new Message(FileSizeValidator::MESSAGE_MAX, ['max_size' => 500])
            ],
        ];
    }
}