<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Processor\FileMoverProcessor;
use Dms\Core\Form\Field\Processor\Validator\FileExtensionValidator;
use Dms\Core\Form\Field\Processor\Validator\FileSizeValidator;
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
    public function extension($extension)
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
        return $this
                ->attr(FileType::ATTR_EXTENSIONS, $extensions)
                ->validate(new FileExtensionValidator($this->getCurrentProcessedType(), $extensions));
    }

    /**
     * Validates the file size is above the supplied size.
     *
     * @param int $bytes
     *
     * @return static
     */
    public function minSize($bytes)
    {
        return $this
                ->attr(FileType::ATTR_MIN_SIZE, $bytes)
                ->validate(new FileSizeValidator($this->getCurrentProcessedType(), $bytes, null));
    }

    /**
     * Validates the file size is less than the supplied size.
     *
     * @param int $bytes
     *
     * @return static
     */
    public function maxSize($bytes)
    {
        return $this
                ->attr(FileType::ATTR_MAX_SIZE, $bytes)
                ->validate(new FileSizeValidator($this->getCurrentProcessedType(), null, $bytes));
    }

    /**
     * Moves the file to the supplied file path.
     *
     * @param string $path
     *
     * @return static
     */
    public function moveToWithClientsFileName($path)
    {
        return $this
                ->process(FileMoverProcessor::withClientFileName($this->isImage(), $path));
    }

    /**
     * Moves the file to the supplied file path.
     *
     * @param string $path
     * @param int    $fileNameLength
     *
     * @return static
     */
    public function moveToPathWithRandomFileName($path, $fileNameLength = 16)
    {
        return $this
                ->process(FileMoverProcessor::withRandomFileName($this->isImage(), $path, $fileNameLength));
    }

    protected function isImage()
    {
        return false;
    }
}