<?php

namespace Iddigital\Cms\Core\Module\Handler;

use Iddigital\Cms\Core\Module\InvalidHandlerClassException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;

/**
 * The action handler base for handlers with a dto type
 * defined by the handler method.
 *
 * Subclasses of this class must implement a handle method with
 * one of the following signatures:
 *
 * public function handle(<dto type> $data) : IDataTransferObject|null
 *
 * This is not explicitly defined as an abstract method
 * to allow subclasses to define the dto type in the signature
 * of the handle method. A trade-off due to PHP lack of generic types.
 *
 * @method IDataTransferObject|null handle(IDataTransferObject $data)
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class ParameterizedActionHandler extends ReflectionBasedActionHandler
{
    public function __construct()
    {
        parent::__construct($this->loadDtoTypeFromHandleMethod(), $this->getReturnType());
    }

    private function loadDtoTypeFromHandleMethod()
    {
        try {
            $reflection = new \ReflectionMethod(get_class($this), 'handle');

            if (!$reflection->isPublic()) {
                throw InvalidHandlerClassException::format(
                        'Invalid handler class %s: handle method must be public',
                        get_class($this)
                );
            }

            return $this->getDtoTypeFromParameter($reflection, 'method');

        } catch (\ReflectionException $e) {
            throw InvalidHandlerClassException::format(
                    'Invalid handler class %s: missing method handle([<dto type> $data])',
                    get_class($this)
            );
        }
    }

    /**
     * Gets the return dto type of the action handler.
     *
     * @return string|null
     */
    abstract protected function getReturnType();

    /**
     * Runs the action handler.
     *
     * @param IDataTransferObject $data
     *
     * @return IDataTransferObject
     */
    protected function runHandler(IDataTransferObject $data)
    {
        return $this->handle($data);
    }
}