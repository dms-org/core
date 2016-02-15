<?php declare(strict_types = 1);

namespace Dms\Core\Module\Handler;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form;
use Dms\Core\Module\IActionHandler;
use Dms\Core\Util\Debug;

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
     * @param string|null $returnType
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $returnType = null)
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