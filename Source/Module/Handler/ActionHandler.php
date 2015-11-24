<?php

namespace Iddigital\Cms\Core\Module\Handler;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Module\IActionHandler;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The action handler base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class ActionHandler implements IActionHandler
{
    /**
     * @var string|null
     */
    private $returnType;

    /**
     * ActionHandler constructor.
     *
     * @param string $returnType
     *
     * @throws InvalidArgumentException
     */
    public function __construct($returnType)
    {
        if ($returnType && !class_exists($returnType, true) && !interface_exists($returnType, true)) {
            throw InvalidArgumentException::format(
                    'Invalid return type for action handler: must be a valid class or interface, %s given',
                    $returnType
            );
        }

        $this->returnType = $returnType;
    }

    /**
     * @inheritdoc
     */
    final public function getReturnTypeClass()
    {
        return $this->returnType;
    }

    /**
     * @param $result
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    final protected function verifyResult($result)
    {
        $returnType = $this->returnType;
        if ($returnType === null) {
            if ($result !== null) {
                throw TypeMismatchException::format(
                        'Invalid return type from action handler %s: expecting null, %s given',
                        get_class($this), Debug::getType($result)
                );
            }

            return $result;
        }

        if (!($result instanceof $returnType)) {
            throw TypeMismatchException::format(
                    'Invalid return value from action handler %s: expecting %s, %s given',
                    get_class($this), $returnType, Debug::getType($result)
            );
        }

        return $result;
    }
}