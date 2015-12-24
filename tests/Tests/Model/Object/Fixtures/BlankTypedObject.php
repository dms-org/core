<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BlankTypedObject extends TypedObject
{
    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {

    }
}