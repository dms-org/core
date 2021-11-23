<?php declare(strict_types = 1);

namespace Dms\Core\Module\Handler;

use Dms\Core\Form;
use Dms\Core\Module\InvalidHandlerClassException;

/**
 * The reflection based action handler.
 *
 * This uses reflection to infer the dto type from a typehint.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class ReflectionBasedActionHandler extends ParameterizedActionHandlerBase
{
    final protected function getTypeFromParameter(
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
        
        $typeHint = @$parameters[0]->getClass();

        if (!$typeHint) {
            throw InvalidHandlerClassException::format(
                    'Invalid handler %s: handle %s parameter does not typehint a class',
                    get_class($this), $functionType
            );
        }

        return $typeHint->getName();
    }
}