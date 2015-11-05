<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Model\IDataTransferObject;
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
     * @param string|null $returnDtoType
     *
     * @throws InvalidHandlerClassException
     */
    public function __construct(callable $handler, $returnDtoType = null)
    {
        list($objectType, $dataDtoType) = $this->loadTypeHintsFromCallable($handler);

        parent::__construct($objectType, $dataDtoType, $returnDtoType);
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
            $dtoTypeHint = $parameters[1]->getClass();

            if (!$dtoTypeHint || !$dtoTypeHint->isSubclassOf(IDataTransferObject::class)) {
                throw InvalidHandlerClassException::format(
                        'Invalid handler callback supplied to %s: second parameter must type hint a subclass of %s, %s given',
                        __CLASS__, $dtoTypeHint ? $dtoTypeHint->getName() : 'none'
                );
            }
        } else {
            $dtoTypeHint = null;
        }

        return [$objectTypeHint->getName(), $dtoTypeHint ? $dtoTypeHint->getName() : null];
    }

    /**
     * Runs the handler.
     *
     * @param object                   $object
     * @param IDataTransferObject|null $data
     *
     * @return IDataTransferObject|null
     */
    protected function runObjectHandler($object, IDataTransferObject $data = null)
    {
        call_user_func($this->handler, $object, $data);
    }
}