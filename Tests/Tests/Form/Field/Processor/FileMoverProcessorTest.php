<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\File\IFile;
use Iddigital\Cms\Core\File\IImage;
use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Form\Field\Processor\FileMoverProcessor;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileMoverProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return FileMoverProcessor::withClientFileName($isImage = false, '/some/dir');
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::object(IFile::class)->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
                [null, null],
                [$this->mockUploadedFile('foo', '/some/dir' . DIRECTORY_SEPARATOR . 'foo', $file = $this->mockFile()), $file],
                [$this->mockUploadedFile('bar', '/some/dir' . DIRECTORY_SEPARATOR . 'bar', $file = $this->mockFile()), $file],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
                [null, null],
                [$file = $this->mockFile(), $file],
                [$file = $this->mockFile(), $file],
        ];
    }

    protected function mockUploadedFile($clientFileName, $expectedMoveToPath = null, $return, &$movePath = null)
    {
        $mock = $this->getMockForAbstractClass(IUploadedFile::class);

        $mock->expects($this->any())
                ->method('getClientFileName')
                ->willReturn($clientFileName);

        if ($expectedMoveToPath) {
            $mock->expects($this->once())
                    ->method('moveTo')
                    ->with($expectedMoveToPath)
                    ->willReturn($return);
        } else {
            $mock->expects($this->once())
                    ->method('moveTo')
                    ->willReturnCallback(function ($path) use (&$movePath, $return) {
                        $movePath = $path;

                        return $return;
                    });
        }

        return $mock;
    }

    protected function mockFile()
    {
        $mock = $this->getMockForAbstractClass(IFile::class);

        return $mock;
    }

    public function testRandomFileName()
    {
        $processor = FileMoverProcessor::withRandomFileName($isImage = false, '/some/dir', 6);
        $messages  = [];

        $return1 = $processor->process($this->mockUploadedFile('foo', null, $file1 = $this->mockFile(), $filePath1), $messages);
        $return2 = $processor->process($this->mockUploadedFile('bar', null, $file2 = $this->mockFile(), $filePath2), $messages);

        $this->assertStringStartsWith('/some/dir' . DIRECTORY_SEPARATOR, $filePath1);
        $this->assertStringStartsWith('/some/dir' . DIRECTORY_SEPARATOR, $filePath2);
        $this->assertNotEquals($filePath1, $filePath2);
        $this->assertSame($file1, $return1);
        $this->assertSame($file2, $return2);
    }

    public function testImage()
    {
        $processor = FileMoverProcessor::withClientFileName($isImage = true, '/some/dir');

        $this->assertEquals(Type::object(IImage::class)->nullable(), $processor->getProcessedType());
    }
}