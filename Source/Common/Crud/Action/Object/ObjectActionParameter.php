<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
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
     * @var object|null
     */
    protected $data;

    /**
     * ObjectActionParameter constructor.
     *
     * @param object      $object
     * @param object|null $data
     */
    public function __construct($object, $data = null)
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

        $class->property($this->data)->nullable()->asObject();
    }

    /**
     * @return bool
     */
    public function hasData()
    {
        return $this->data !== null;
    }

    /**
     * @return object|null
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