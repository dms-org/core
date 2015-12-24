<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\File\IUploadedFile;
use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Builder\FileFieldBuilder;
use Dms\Core\Form\Field\Processor\Validator\FileExtensionValidator;
use Dms\Core\Form\Field\Processor\Validator\FileSizeValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\Field\Processor\Validator\UploadedFileValidator;
use Dms\Core\Form\Field\Type\FileType;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return FileFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->file();
    }

    protected function mockUploadedFile($size, $extension = null)
    {
        $file = $this->getMockForAbstractClass(IUploadedFile::class);

        $file->expects($this->any())
                ->method('getSize')
                ->willReturn($size);

        $file->expects($this->any())
                ->method('getExtension')
                ->willReturn($extension);

        $file->expects($this->any())
                ->method('hasUploadedSuccessfully')
                ->willReturn(true);

        $file->expects($this->any())
                ->method('getUploadError')
                ->willReturn(UPLOAD_ERR_OK);

        return $file;
    }

    public function testFieldWithProcessors()
    {
        $field = $this->field()
                ->minSize(100)
                ->maxSize(2000)
                ->extension('pdf')
                ->build();

        $this->assertEquals([
                new TypeValidator(Type::object(IUploadedFile::class)->nullable()),
                new UploadedFileValidator(Type::object(IUploadedFile::class)->nullable()),
                new FileExtensionValidator(Type::object(IUploadedFile::class)->nullable(), ['pdf']),
                new FileSizeValidator(Type::object(IUploadedFile::class)->nullable(), 100, 2000),
        ], $field->getProcessors());

        $this->assertSame(100, $field->getType()->get(FileType::ATTR_MIN_SIZE));
        $this->assertSame(['pdf'], $field->getType()->get(FileType::ATTR_EXTENSIONS));
        $this->assertSame(2000, $field->getType()->get(FileType::ATTR_MAX_SIZE));
        $validFile = $this->mockUploadedFile(500, 'pdf');
        $this->assertSame($validFile, $field->process($validFile));

        $invalidFile = $this->mockUploadedFile(50, 'pdf');
        $this->assertFieldThrows($field, $invalidFile, [
                new Message(FileSizeValidator::MESSAGE_MIN, [
                        'field'    => 'Name',
                        'input'    => $invalidFile,
                        'min_size' => 100,
                ])
        ]);

        $invalidFile = $this->mockUploadedFile(10000, 'pdf');
        $this->assertFieldThrows($field, $invalidFile, [
                new Message(FileSizeValidator::MESSAGE_MAX, [
                        'field'    => 'Name',
                        'input'    => $invalidFile,
                        'max_size' => 2000,
                ])
        ]);

        $invalidFile = $this->mockUploadedFile(1000, 'png');
        $this->assertFieldThrows($field, $invalidFile, [
                new Message(FileExtensionValidator::MESSAGE, [
                        'field'      => 'Name',
                        'input'      => $invalidFile,
                        'extensions' => 'pdf',
                ])
        ]);

        $this->assertEquals(Type::object(IUploadedFile::class)->nullable(), $field->getProcessedType());
    }
}