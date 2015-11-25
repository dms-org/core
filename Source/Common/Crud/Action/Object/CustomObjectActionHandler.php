<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Module\InvalidHandlerClassException;
use Iddigital\Cms\Core\Util\Reflection;

/**
 * The custom object action handler class, delegates to a callback.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomObjectActionHandler extends ObjectActionHandler
{
    /**
     * @var callable
     */
    protected $handler;

    /**
     * @param callable    $handler
     * @param string|null $returnType
     * @param string|null $objectType
     * @param string|null $dataParameterType
     *
     * @throws InvalidHandlerClassException
     */
    public function __construct(callable $handler, $returnType = null, $objectType = null, $dataParameterType = null)
    {
        if (!$objectType) {
            list($objectType, $dataParameterType) = $this->loadTypeHintsFromCallable($handler);
        }

        parent::__construct($objectType, $dataParameterType, $returnType);
        $this->handler = $handler;
    }

    protected function loadTypeHintsFromCallable(callable $handler)
    {
        $reflection       = Reflection::fromCallable($handler);
        $parametersAmount = $reflection->getNumberOfParameters();

        if ($parametersAmount !== 1 && $parametersAmount !== 2) {
            throw InvalidHandlerClassException::format(
                    'Invalid handler callback supplied to %s: must have 1 or 2 parameters, %d given',
                    __CLASS__, $parametersAmount
            );
        }

        $parameters     = $reflection->getParameters();
        $objectTypeHint = $parameters[0]->getClass();

        if (!$objectTypeHint) {
            throw InvalidHandlerClassException::format(
                    'Invalid handler callback supplied to %s: first parameter must type hint a class',
                    __CLASS__
            );
        }

        if (isset($parameters[1])) {
            $dataParameterTypeHint = $parameters[1]->getClass();

            if (!$dataParameterTypeHint) {
                throw InvalidHandlerClassException::format(
                        'Invalid handler callback supplied to %s: second parameter must type hint a class, %s given',
                        __CLASS__, $dataParameterTypeHint ? $dataParameterTypeHint->getName() : 'none'
                );
            }
        } else {
            $dataParameterTypeHint = null;
        }

        return [$objectTypeHint->getName(), $dataParameterTypeHint ? $dataParameterTypeHint->getName() : null];
    }

    /**
     * Runs the handler.
     *
     * @param object      $object
     * @param object|null $data
     *
     * @return object|null
     */
    protected function runObjectHandler($object, $data = null)
    {
        return call_user_func($this->handler, $object, $data);
    }
}