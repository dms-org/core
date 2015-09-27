<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\EmbeddedVersion;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithEmbeddedVersion extends Entity
{
    /**
     * @var VersionValueObject
     */
    public $version;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, VersionValueObject $version = null)
    {
        parent::__construct($id);
        $this->version = $version ?: new VersionValueObject();
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->version)->asObject(VersionValueObject::class);
    }
}