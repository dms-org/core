<?php

namespace Iddigital\Cms\Core\File;

/**
 * The uploaded image interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IUploadedImage extends IUploadedFile, IImage
{
    /**
     * {@inheritdoc}
     *
     * @return IImage
     */
    public function moveTo($fullPath);
}
