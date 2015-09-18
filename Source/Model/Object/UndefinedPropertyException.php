<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;

/**
 * Exception for an undefined class property.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UndefinedPropertyException extends Exception\BaseException
{
    public function __construct($class, $name)
    {
        parent::__construct(
                "Cannot access property: {$class}::\${$name} is not defined"
        );
    }

}