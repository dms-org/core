<?php declare(strict_types = 1);

namespace Dms\Core\Util\Metadata;

/**
 * The metadata trait.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
trait MetadataTrait
{
    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @inheritdoc
     */
    final public function hasMetadata(string $key) : bool
    {
        return isset($this->metadata[$key]);
    }

    /**
     * @inheritdoc
     */
    final public function getMetadata(string $key)
    {
        return $this->metadata[$key] ?? null;
    }

    /**
     * @inheritdoc
     */
    final public function getAllMetadata() : array
    {
        return $this->metadata;
    }
}