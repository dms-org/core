<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id;

use Dms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmptyEntitySubclass extends EmptyEntity
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