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
     * @param string|null $returnType
     * @param string|null $parameterType
     *
     * @throws \Iddigital\Cms\Core\Module\InvalidHandlerClassException
     */
    public function __construct(callable $handlerCallback, $returnType = null, $parameterType = null)
    {
        parent::__construct(
                $parameterType ?: $this->getTypeFromParameter(Reflection::fromCallable($handlerCallback), 'function'),
                $returnType
        );
        $this->handlerCallback = $handlerCallback;
    }

    /**
     * Runs the action handler.
     *
     * @param object $data
     *
     * @return object|null
     */
    protected function runHandler($data)
    {
        return call_user_func($this->handlerCallback, $data);
    }
}