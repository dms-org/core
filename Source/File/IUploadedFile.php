<?php

namespace Iddigital\Cms\Core\File;

/**
 * The uploaded file interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IUploadedFile extends IFile
{
    /**
     * Whether the upload was a success
     * 
     * @return bool
     */
    public function isValid();

    /**
     * Gets the client's file name including the extension.
     *
     * @return string
     */
    public function getClientFileName();

    /**
     * Gets the client's file mime type
     *
     * @return string
     */
    public function getClientMimeType();

    /**
     * Moves the file to the supplied path
     *
     * @param string $fullPath The file path including the file name
     *
     * @return IFile
     */
    public function moveTo($fullPath);
}
