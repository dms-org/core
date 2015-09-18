<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Builder\FileFieldBuilder;
use Iddigital\Cms\Core\Form\Field\Processor\FileMoverProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\FileExtensionValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\FileSizeValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\TypeValidator;
use Iddigital\Cms\Core\Form\Field\Type\FileType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
                new FileSizeValidator(Type::object(IUploadedFile::class)->nullable(), 100, null),
                new FileSizeValidator(Type::object(IUploadedFile::class)->nullable(), null, 2000),
                new FileExtensionValidator(Type::object(IUploadedFile::class)->nullable(), ['pdf'])
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