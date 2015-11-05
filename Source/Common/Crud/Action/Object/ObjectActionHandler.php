<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Module\Handler\ParameterizedActionHandlerBase;

/**
 * The object action handler base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectActionHandler extends ParameterizedActionHandlerBase implements IObjectActionHandler
{
    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var string|null
     */
    protected $dataDtoType;

    /**
     * ObjectActionHandler constructor.
     *
     * @param string      $objectType
     * @param string|null $dataDtoType
     * @param string|null $returnDtoType
     */
    public function __construct($objectType, $dataDtoType, $returnDtoType = null)
    {
        parent::__construct(ObjectActionParameter::class, $returnDtoType);

        $this->objectType  = $objectType;
        $this->dataDtoType = $dataDtoType;
    }

    /**
     * @inheritDoc
     */
    final public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @inheritDoc
     */
    final public function hasDataDtoType()
    {
        return $this->dataDtoType !== null;
    }

    /**
     * @inheritDoc
     */
    final public function getDataDtoType()
    {
        return $this->dataDtoType;
    }

    /**
     * @inheritDoc
     */
    final protected function runHandler(IDataTransferObject $data)
    {
        /** @var ObjectActionParameter $data */
        $this->runOnObject($data->getObject(), $data->getData());
    }

    /**
     * @inheritDoc
     */
    final public function runOnObject($object, IDataTransferObject $data = null)
    {
        if (!($object instanceof $this->objectType)) {
            throw TypeMismatchException::argument(__METHOD__, 'object', $this->objectType, $object);
        }

        if ($this->dataDtoType && !($data instanceof $this->dataDtoType)) {
            throw TypeMismatchException::argument(__METHOD__, 'data', $this->dataDtoType, $data);
        }

        return $this->runObjectHandler($object, $data);
    }

    /**
     * Runs the handler.
     *
     * @param object                   $object
     * @param IDataTransferObject|null $data
     *
     * @return IDataTransferObject|null
     */
    abstract protected function runObjectHandler($object, IDataTransferObject $data = null);
}