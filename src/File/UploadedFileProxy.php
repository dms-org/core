<?php

namespace Dms\Core\File;
use Dms\Core\Exception\InvalidOperationException;

/**
 * The uploaded file proxy.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UploadedFileProxy implements IUploadedFile
{
    /**
     * @var IFile
     */
    protected $file;

    /**
     * @var bool
     */
    private $actuallyMove;

    /**
     * UploadedFileProxy constructor.
     *
     * @param IFile $file
     * @param bool  $actuallyMove
     */
    public function __construct(IFile $file, bool $actuallyMove = false)
    {
        $this->file = $file;
        $this->actuallyMove = $actuallyMove;
    }

    /**
     * Gets the file name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->file->getName();
    }

    /**
     * Gets the client's file name including the extension or fall back to the
     * actual file name.
     *
     * @return string
     */
    public function getClientFileNameWithFallback() : string
    {
        return $this->file->getClientFileNameWithFallback();
    }

    /**
     * Gets the file extension.
     *
     * @return string
     */
    public function getExtension() : string
    {
        return $this->file->getExtension();
    }

    /**
     * Gets the full file path including the file name.
     *
     * @return string
     */
    public function getFullPath() : string
    {
        return $this->file->getFullPath();
    }

    /**
     * Gets the file size in bytes.
     *
     * @return int
     * @throws InvalidOperationException if the file does not exist
     */
    public function getSize() : int
    {
        return $this->file->getSize();
    }

    /**
     * Gets whether the file exists.
     *
     * @return bool
     */
    public function exists() : bool
    {
        return $this->file->exists();
    }

    /**
     * Get the file info
     *
     * @return \SplFileInfo
     */
    public function getInfo()
    {
        return $this->file->getInfo();
    }

    /**
     * Returns whether file uploaded successfully.
     *
     * @return bool
     */
    public function hasUploadedSuccessfully() : bool
    {
        return true;
    }

    /**
     * Gets the status of the upload.
     *
     * This returns one of the UPLOAD_ERR_* constants.
     *
     * @return int
     */
    public function getUploadError() : int
    {
        return UPLOAD_ERR_OK;
    }

    /**
     * Gets the client's file name including the extension.
     *
     * @return string|null
     */
    public function getClientFileName()
    {
        return $this->file->getClientFileName();
    }

    /**
     * Gets the client's file mime type
     *
     * @return string|null
     */
    public function getClientMimeType()
    {
        return null;
    }

    /**
     * Moves the file to the supplied path
     *
     * @param string $fullPath The file path including the file name
     *
     * @return IFile
     */
    public function moveTo(string $fullPath) : IFile
    {
        if ($this->actuallyMove) {
            return $this->file->moveTo($fullPath);
        }

        return $this->file;
    }
}