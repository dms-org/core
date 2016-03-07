<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\File\IFile;
use Dms\Core\File\IImage;
use Dms\Core\File\IUploadedFile;
use Dms\Core\File\UploadedFileProxy;
use Dms\Core\File\UploadedImageProxy;
use Dms\Core\Form\Field\Processor\FileMoverProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;

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
        return FileMoverProcessor::withClientFileName(IFile::class, '/some/dir');
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
                [$file = $this->mockFile(), new UploadedFileProxy($file)],
                [$file = $this->mockFile(), new UploadedFileProxy($file)],
                [$image = $this->mockImage(), new UploadedImageProxy($image)],
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

    protected function mockImage()
    {
        $mock = $this->getMockForAbstractClass(IImage::class);

        return $mock;
    }

    public function testRandomFileName()
    {
        $processor = FileMoverProcessor::withRandomFileName(IFile::class, '/some/dir', 6);
        $messages  = [];

        $return1 = $processor->process($this->mockUploadedFile('foo', null, $file1 = $this->mockFile(), $filePath1), $messages);
        $return2 = $processor->process($this->mockUploadedFile('bar', null, $file2 = $this->mockFile(), $filePath2), $messages);

        $this->assertStringStartsWith('/some/dir' . DIRECTORY_SEPARATOR, $filePath1);
        $this->assertStringStartsWith('/some/dir' . DIRECTORY_SEPARATOR, $filePath2);
        $this->assertNotEquals($filePath1, $filePath2);
        $this->assertSame($file1, $return1);
        $this->assertSame($file2, $return2);
    }

    public function testStaticFileName()
    {
        $processor = FileMoverProcessor::withFileName(IFile::class, '/some/dir', 'some-name.txt');
        $messages  = [];

        $return1 = $processor->process($this->mockUploadedFile('foo', null, $file1 = $this->mockFile(), $filePath1), $messages);
        $return2 = $processor->process($this->mockUploadedFile('bar', null, $file2 = $this->mockFile(), $filePath2), $messages);

        $this->assertSame('/some/dir' . DIRECTORY_SEPARATOR . 'some-name.txt', $filePath1);
        $this->assertSame('/some/dir' . DIRECTORY_SEPARATOR . 'some-name.txt', $filePath2);
        $this->assertSame($file1, $return1);
        $this->assertSame($file2, $return2);
    }
    public function testImage()
    {
        $processor = FileMoverProcessor::withClientFileName(IImage::class, '/some/dir');

        $this->assertEquals(Type::object(IImage::class)->nullable(), $processor->getProcessedType());
    }
}