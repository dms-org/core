<?php

namespace Dms\Core\File;

/**
 * The uploaded file interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IUploadedFile extends IFile
{
    /**
     * Returns whether file uploaded successfully.
     *
     * @return bool
     */
    public function hasUploadedSuccessfully();

    /**
     * Gets the status of the upload.
     *
     * This returns one of the UPLOAD_ERR_* constants.
     *
     * @return int
     */
    public function getUploadError();

    /**
     * Gets the client's file name including the extension.
     *
     * @return string|null
     */
    public function getClientFileName();

    /**
     * Gets the client's file mime type
     *
     * @return string|null
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
