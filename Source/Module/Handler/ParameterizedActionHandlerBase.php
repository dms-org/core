<?php

namespace Iddigital\Cms\Core\Module\Handler;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;
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
     * @param string|null $returnDtoType
     */
    public function __construct($dtoType, $returnDtoType = null)
    {
        parent::__construct($returnDtoType);
        $this->dtoType = $dtoType;
    }

    /**
     * {@inheritDoc}
     */
    final public function getDtoType()
    {
        return $this->dtoType;
    }

    /**
     * {@inheritDoc}
     */
    final public function run(IDataTransferObject $data)
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
     * @param IDataTransferObject $data
     *
     * @return IDataTransferObject|null
     */
    abstract protected function runHandler(IDataTransferObject $data);
}