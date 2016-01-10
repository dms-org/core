<?php

namespace Dms\Core\File;

use Dms\Core\Exception\InvalidOperationException;

/**
 * The file interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IFile
{
    /**
     * Gets the file name.
     * 
     * @return string
     */
    public function getName();

    /**
     * Gets the client's file name including the extension.
     *
     * @return string|null
     */
    public function getClientFileName();

    /**
     * Gets the client's file name including the extension or fall back to the
     * actual file name.
     *
     * @return string
     */
    public function getClientFileNameWithFallback();

    /**
     * Gets the file extension.
     *
     * @return string
     */
    public function getExtension();

    /**
     * Gets the full file path including the file name.
     *
     * @return string
     */
    public function getFullPath();

    /**
     * Gets the file size in bytes.
     *
     * @return int
     * @throws InvalidOperationException if the file does not exist
     */
    public function getSize();

    /**
     * Gets whether the file exists.
     *
     * @return bool
     */
    public function exists();

    /**
     * Get the file info
     *
     * @return \SplFileInfo
     */
    public function getInfo();
}
