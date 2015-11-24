<?php

namespace Iddigital\Cms\Core\Module\Handler;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;

/**
 * The parameterized action handler base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class ParameterizedActionHandlerBase extends ActionHandler implements IParameterizedActionHandler
{
    /**
     * @var string
     */
    private $dtoType;

    /**
     * ActionHandler constructor.
     *
     * @param string      $dtoType
     * @param string|null $returnType
     */
    public function __construct($dtoType, $returnType = null)
    {
        parent::__construct($returnType);
        $this->dtoType = $dtoType;
    }

    /**
     * {@inheritDoc}
     */
    final public function getParameterTypeClass()
    {
        return $this->dtoType;
    }

    /**
     * {@inheritDoc}
     */
    final public function run($data)
    {
        $dtoType = $this->dtoType;

        if (!($data instanceof $dtoType)) {
            throw TypeMismatchException::argument(__METHOD__, 'data', $dtoType, $data);
        }

        return $this->verifyResult($this->runHandler($data));
    }

    /**
     * Runs the action handler.
     *
     * @param object $data
     *
     * @return object|null
     */
    abstract protected function runHandler($data);
}