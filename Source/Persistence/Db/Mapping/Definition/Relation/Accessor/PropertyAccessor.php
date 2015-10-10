<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\IAccessor;

/**
 * The property accessor class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyAccessor implements IAccessor
{
    /**
     * @var string
     */
    protected $propertyName;

    /**
     * PropertyAccessor constructor.
     *
     * @param string $propertyName
     */
    public function __construct($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @param ITypedObject $object
     * @param array        $properties
     *
     * @return mixed
     */
    public function get(ITypedObject $object, array $properties)
    {
        return $properties[$this->propertyName];
    }

    /**
     * @param ITypedObject $object
     * @param array        $properties
     * @param mixed        $value
     *
     * @return void
     */
    public function set(ITypedObject $object, array &$properties, $value)
    {
        $properties[$this->propertyName] = $value;
    }
}