<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\DateTimeVersion;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeVersionedEntity extends Entity
{
    /**
     * @var \DateTimeImmutable|null
     */
    public $version;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, \DateTimeImmutable $version = null)
    {
        parent::__construct($id);
        if ($version) {
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
        $class->property($this->version)->asObject(\DateTimeImmutable::class);
    }
}