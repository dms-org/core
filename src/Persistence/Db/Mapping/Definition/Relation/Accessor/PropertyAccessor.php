<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\IAccessor;

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
    private $className;

    /**
     * @var FinalizedPropertyDefinition
     */
    protected $property;

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * PropertyAccessor constructor.
     *
     * @param string                      $className
     * @param FinalizedPropertyDefinition $property
     */
    public function __construct(string $className, FinalizedPropertyDefinition $property)
    {
        $this->className = $className;
        $this->property     = $property;
        $this->propertyName = $property->getName();
    }

    /**
     * @inheritDoc
     */
    public function getDebugName() : string
    {
        return $this->className . '::$' . $this->propertyName;
    }

    /**
     * @inheritDoc
     */
    public function getCompatibleType()
    {
        return $this->property->getType();
    }

    /**
     * @return string
     */
    public function getPropertyName() : string
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