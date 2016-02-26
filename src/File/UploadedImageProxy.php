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
     * UploadedImageProxy constructor.
     *
     * @param IImage   $file
     * @param callable $moveCallback
     * @param callable $copyCallback
     */
    public function __construct(IImage $file, callable $moveCallback = null, callable $copyCallback = null)
    {
        parent::__construct($file, $moveCallback, $copyCallback);
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