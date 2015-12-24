<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ToOneRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RecursiveEntity extends Entity
{
    /**
     * @var RecursiveEntity
     */
    public $parent;

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->parent)->asObject(__CLASS__);
    }
}