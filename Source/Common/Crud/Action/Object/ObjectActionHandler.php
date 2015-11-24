<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
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
     * @param string|null $dataType
     * @param string|null $returnType
     */
    public function __construct($objectType, $dataType, $returnType = null)
    {
        parent::__construct(ObjectActionParameter::class, $returnType);

        $this->objectType  = $objectType;
        $this->dataDtoType = $dataType;
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
    final protected function runHandler($data)
    {
        /** @var ObjectActionParameter $data */
        $this->runOnObject($data->getObject(), $data->getData());
    }

    /**
     * @inheritDoc
     */
    final public function runOnObject($object, $data = null)
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
     * @param object      $object
     * @param object|null $data
     *
     * @return object|null
     */
    abstract protected function runObjectHandler($object, $data = null);
}