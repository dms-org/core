<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\IntegerVersion;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntegerVersionedEntity extends Entity
{
    /**
     * @var int|null
     */
    public $version;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $version = null)
    {
        parent::__construct($id);
        $this->version = $version;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->version)->nullable()->asInt();
    }
}