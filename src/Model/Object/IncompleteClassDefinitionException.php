<?php

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IncompleteClassDefinitionException extends Exception\BaseException
{
    public function __construct($class, PropertyDefinition $definition)
    {
        parent::__construct(
                "Cannot build {$class}: no type has been defined for {$definition->getClass()}::\${$definition->getName()}"
        );
    }

}