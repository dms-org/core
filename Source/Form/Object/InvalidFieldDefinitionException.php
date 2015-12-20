<?php

namespace Dms\Core\Form\Object;

use Dms\Core\Exception\BaseException;

/**
 * The invalid field definition exception.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidFieldDefinitionException extends BaseException
{
    public function __construct($class, $fieldName)
    {
        parent::__construct(
                "Cannot build form object {$class}: a field '{$fieldName}' was defined which does not link to any properties defined on the class"
        );
    }

}