<?php declare(strict_types = 1);

namespace Dms\Core\Module\Handler;

use Dms\Core\Form;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Util\Reflection;

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
     * @throws \Dms\Core\Module\InvalidHandlerClassException
     */
    public function __construct(callable $handlerCallback, string $returnType = null, string $parameterType = null)
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