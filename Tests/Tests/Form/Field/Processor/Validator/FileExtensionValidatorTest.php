<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\FileExtensionValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileExtensionValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new FileExtensionValidator(Type::object(IUploadedFile::class)->nullable(), ['gif', 'png', 'bmp']);
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
            [$this->mockUploadedFile('gif')],
            [$this->mockUploadedFile('gIf')],
            [$this->mockUploadedFile('GIF')],
            [$this->mockUploadedFile('bmp')],
            [$this->mockUploadedFile('png')],
            [$this->mockUploadedFile('PNG')],
        ];
    }

    protected function mockUploadedFile($extension)
    {
        $file = $this->getMockForAbstractClass(IUploadedFile::class);

        $file->expects($this->any())
            ->method('getExtension')
            ->willReturn($extension);

        return $file;
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [
                $this->mockUploadedFile(''),
                new Message(FileExtensionValidator::MESSAGE, ['extensions' => 'gif, png, bmp'])
            ],
            [
                $this->mockUploadedFile('jpg'),
                new Message(FileExtensionValidator::MESSAGE, ['extensions' => 'gif, png, bmp'])
            ],
        ];
    }
}