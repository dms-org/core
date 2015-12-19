<?php

namespace Iddigital\Cms\Core\File;

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
     * Gets the file extension.
     *
     * @return string
     */
    public function getExtension();

    /**
     * Gets the full file path.
     *
     * @return string
     */
    public function getFullPath();

    /**
     * Gets the file size in bytes.
     *
     * @return int
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
