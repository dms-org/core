<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\IntegerVersion;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

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
        if ($version !== null) {
            $this->version = $version;
        }
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->version)->asInt();
    }
}