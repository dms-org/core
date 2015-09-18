<?php

namespace Iddigital\Cms\Core\File;

/**
 * The image interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IImage extends IFile
{
    /**
     * Gets the image width in pixels.
     * 
     * @return int
     */
    public function getWidth();

    /**
     * Gets the image height in pixels.
     *
     * @return int
     */
    public function getHeight();
}
