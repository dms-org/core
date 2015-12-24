<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\InlineConstraints;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConstrainedEntity extends Entity
{
    /**
     * @var string
     */
    public $indexed;

    /**
     * @var int
     * @unique
     */
    public $unique;

    /**
     * @var string
     */
    public $defaultIndexed;

    /**
     * @var int
     * @unique
     */
    public $defaultUnique;

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->indexed)->asString();

        $class->property($this->unique)->asInt();

        $class->property($this->defaultIndexed)->asString();

        $class->property($this->defaultUnique)->asInt();
    }
}