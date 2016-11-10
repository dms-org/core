<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\File\IFile;
use Dms\Core\Form\Field\Processor\FileMoverProcessor;
use Dms\Core\Form\Field\Type\FileType;

/**
 * The file field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileFieldBuilder extends FieldBuilderBase
{
    /**
     * Validates the file has the supplied extension.
     *
     * @param string $extension
     *
     * @return static
     */
    public function extension(string $extension)
    {
        return $this->extensions([$extension]);
    }

    /**
     * Validates the file has one of the supplied extensions.
     *
     * @param string[] $extensions
     *
     * @return static
     */
    public function extensions(array $extensions)
    {
        return $this->attr(FileType::ATTR_EXTENSIONS, $extensions);
    }

    /**
     * Validates the file size is above the supplied size.
     *
     * @param int $bytes
     *
     * @return static
     */
    public function minSize(int $bytes)
    {
        return $this->attr(FileType::ATTR_MIN_SIZE, $bytes);
    }

    /**
     * Validates the file size is less than the supplied size.
     *
     * @param int $bytes
     *
     * @return static
     */
    public function maxSize(int $bytes)
    {
        return $this->attr(FileType::ATTR_MAX_SIZE, $bytes);
    }

    /**
     * Moves the file to the supplied file path.
     *
     * @param string $path
     * @param string $fileName
     *
     * @return static
     */
    public function moveToPathWithStaticFileName(string $path, string $fileName)
    {
        return $this->process(FileMoverProcessor::withFileName($this->movedClassName(), $path, $fileName));
    }

    /**
     * Moves the file to the supplied file path.
     *
     * @param string $path
     * @param string $fileName
     *
     * @return static
     */
    public function moveToPathWithStaticFileNameAndClientExtension(string $path, string $fileName)
    {
        return $this->process(FileMoverProcessor::withFileNameWithClientExtension($this->movedClassName(), $path, $fileName));
    }

    /**
     * Moves the file to the supplied file path.
     *
     * @param string $path
     *
     * @return static
     */
    public function moveToPathWithClientsFileName(string $path)
    {
        return $this->process(FileMoverProcessor::withClientFileName($this->movedClassName(), $path));
    }

    /**
     * Moves the file to the supplied file path.
     *
     * @param string $path
     * @param int    $fileNameLength
     *
     * @return static
     */
    public function moveToPathWithRandomFileName(string $path, int $fileNameLength = 16)
    {
        return $this->process(FileMoverProcessor::withRandomFileName($this->movedClassName(), $path, $fileNameLength));
    }

    protected function movedClassName() : string
    {
        return IFile::class;
    }
}