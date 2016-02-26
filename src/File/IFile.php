<?php declare(strict_types = 1);

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
    public function getName() : string;

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
    public function getClientFileNameWithFallback() : string;

    /**
     * Gets the file extension.
     *
     * @return string
     */
    public function getExtension() : string;

    /**
     * Gets the full file path including the file name.
     *
     * @return string
     */
    public function getFullPath() : string;

    /**
     * Gets the file size in bytes.
     *
     * @return int
     * @throws InvalidOperationException if the file does not exist
     */
    public function getSize() : int;

    /**
     * Gets whether the file exists.
     *
     * @return bool
     */
    public function exists() : bool;

    /**
     * Get the file info
     *
     * @return \SplFileInfo
     */
    public function getInfo();

    /**
     * Moves the file to the supplied path
     *
     * @param string $fullPath The file path including the file name
     *
     * @return IFile
     */
    public function moveTo(string $fullPath) : IFile;

    /**
     * Copies the file to the supplied path
     *
     * @param string $fullPath The file path including the file name
     *
     * @return IFile
     */
    public function copyTo(string $fullPath) : IFile;
}
