<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\EmbeddedSubclassWithToManyRelation;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RootEntity extends Entity
{
    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {

    }
}