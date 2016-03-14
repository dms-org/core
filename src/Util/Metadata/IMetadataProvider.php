<?php declare(strict_types = 1);

namespace Dms\Core\Util\Metadata;

/**
 * The metadata provider interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IMetadataProvider
{
    /**
     * Returns whether metadata is set for the supplied key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasMetadata(string $key) : bool;

    /**
     * Gets the value for the supplied metadata or null if no value
     * is set.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getMetadata(string $key);
}