<?php declare(strict_types = 1);

namespace Dms\Core\Module\Handler;

use Dms\Core\Form;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Module\InvalidHandlerClassException;

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
 * @method object|null handle(object $data)
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

            return $this->getTypeFromParameter($reflection, 'method');

        } catch (\ReflectionException $e) {
            throw InvalidHandlerClassException::format(
                    'Invalid handler class %s: missing method handle([<dto type> $data])',
                    get_class($this)
            );
        }
    }

    /**
     * Gets the return type of the action handler.
     *
     * @return string|null
     */
    abstract protected function getReturnType();

    /**
     * Runs the action handler.
     *
     * @param object $data
     *
     * @return object
     */
    protected function runHandler($data)
    {
        return $this->handle($data);
    }
}