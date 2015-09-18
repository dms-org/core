<?php

namespace Iddigital\Cms\Core\Module\Handler;

use Iddigital\Cms\Core\Module\InvalidHandlerClassException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;

/**
 * The reflection based action handler.
 *
 * This uses reflection to infer the dto type from a typehint.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class ReflectionBasedActionHandler extends ParameterizedActionHandlerBase
{
    final protected function getDtoTypeFromParameter(
            \ReflectionFunctionAbstract $function,
            $functionType
    ) {
        $parameters = $function->getParameters();

        if (count($parameters) !== 1) {
            throw InvalidHandlerClassException::format(
                    'Invalid handler %s: handle %s must have one parameter',
                    get_class($this), $functionType
            );
        }
        $typeHint = $parameters[0]->getClass();

        if (!$typeHint) {
            throw InvalidHandlerClassException::format(
                    'Invalid handler %s: handle %s parameter does not have a typehint',
                    get_class($this), $functionType
            );
        } elseif (!$typeHint->isSubclassOf(IDataTransferObject::class)) {
            throw InvalidHandlerClassException::format(
                    'Invalid handler %s: handle %s parameter typehint must be a subclass of %s, %s given',
                    get_class($this), $functionType,
                    IDataTransferObject::class,
                    $typeHint->getName()
            );
        }

        return $typeHint->getName();
    }
}