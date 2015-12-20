<?php

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;

/**
 * Exception for an inaccessible class property.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InaccessiblePropertyException extends Exception\BaseException
{
    public function __construct($class, $name, $accessedFromClass)
    {
        $accessedFromClass = $accessedFromClass ?: 'global scope';
        parent::__construct(
                "Cannot access property: {$class}::\${$name} is not accessible from {$accessedFromClass}"
        );
    }

}