<?php

namespace Iddigital\Cms\Core\Module\Handler;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Util\Reflection;

/**
 * The action handler base for handlers with a dto type
 * defined by a callable parameter.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class CustomParameterizedActionHandler extends ReflectionBasedActionHandler
{
    /**
     * @var callable
     */
    private $handlerCallback;

    /**
     * @param callable    $handlerCallback
     * @param string|null $returnDtoType
     * @param string|null $parameterDtoType
     *
     * @throws \Iddigital\Cms\Core\Module\InvalidHandlerClassException
     */
    public function __construct(callable $handlerCallback, $returnDtoType = null, $parameterDtoType = null)
    {
        parent::__construct(
                $parameterDtoType ?: $this->getDtoTypeFromParameter(Reflection::fromCallable($handlerCallback), 'function'),
                $returnDtoType
        );
        $this->handlerCallback = $handlerCallback;
    }

    /**
     * Runs the action handler.
     *
     * @param IDataTransferObject $data
     *
     * @return IDataTransferObject|null
     */
    protected function runHandler(IDataTransferObject $data)
    {
        return call_user_func($this->handlerCallback, $data);
    }
}