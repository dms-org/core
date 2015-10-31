<?php

namespace Iddigital\Cms\Core\Common\Crud\Action;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\DataTransferObject;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The object action dto class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectActionParameter extends DataTransferObject
{
    /**
     * @var object
     */
    protected $object;

    /**
     * @var IDataTransferObject|null
     */
    protected $data;

    /**
     * ObjectActionParameter constructor.
     *
     * @param object                   $object
     * @param IDataTransferObject|null $data
     */
    public function __construct($object, IDataTransferObject $data = null)
    {
        InvalidArgumentException::verify(is_object($object), 'Expecting object, %s given', Debug::getType($object));

        parent::__construct();
        $this->object = $object;
        $this->data   = $data;
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->object)->asObject();

        $class->property($this->data)->nullable()->asObject(IDataTransferObject::class);
    }

    /**
     * @return bool
     */
    public function hasData()
    {
        return $this->data !== null;
    }

    /**
     * @return IDataTransferObject|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }
}