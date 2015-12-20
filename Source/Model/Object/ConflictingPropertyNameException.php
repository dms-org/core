<?php

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;

/**
 * Exception for multiple private properties of the same defined
 * within a class hierarchy.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConflictingPropertyNameException extends Exception\BaseException
{
    public function __construct($class, $propertyClass1, $propertyClass2, $name)
    {
        parent::__construct(
                "Cannot build {$class}: private property {$propertyClass1}::\${$name} conflicts with {$propertyClass2}::\${$name}"
        );
    }

}