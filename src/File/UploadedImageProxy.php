<?php

namespace Dms\Core\File;

use Dms\Core\Exception\InvalidOperationException;

/**
 * The uploaded image proxy.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UploadedImageProxy extends UploadedFileProxy implements IUploadedImage
{
    /**
     * @var IImage
     */
    protected $file;

    /**
     * UploadedFileProxy constructor.
     *
     * @param IImage $file
     * @param bool   $actuallyMove
     */
    public function __construct(IImage $file, bool $actuallyMove = false)
    {
        parent::__construct($file, $actuallyMove);
    }

    /**
     * Returns whether the file is a valid image
     *
     * @return bool
     */
    public function isValidImage() : bool
    {
        return $this->file->isValidImage();
    }

    /**
     * Gets the image width in pixels.
     *
     * @return int
     * @throws InvalidOperationException if the file is not a valid image
     */
    public function getWidth() : int
    {
        return $this->file->getWidth();
    }

    /**
     * Gets the image height in pixels.
     *
     * @return int
     * @throws InvalidOperationException if the file is not a valid image
     */
    public function getHeight() : int
    {
        return $this->file->getHeight();
    }
}