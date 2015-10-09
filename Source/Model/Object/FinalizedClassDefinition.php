<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The finalized class definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedClassDefinition
{
    /**
     * The instance where all the properties have already been unset.
     *
     * @var TypedObject|null
     */
    private $cleanInstance;

    /**
     * @var string
     */
    private $className;

    /**
     * @var FinalizedPropertyDefinition[]
     */
    private $properties = [];

    /**
     * @var IType[]
     */
    private $propertyTypeMap = [];

    /**
     * @var array
     */
    private $propertyDefaultMap = [];

    /**
     * @var PropertyAccessibility[]
     */
    private $propertyAccessibilityMap = [];

    /**
     * @var PropertyUnsetter
     */
    private $propertyUnsetter = [];

    /**
     * @var bool
     */
    private $isAbstract;

    /**
     * @param \ReflectionClass              $reflection
     * @param TypedObject|null              $cleanInstance
     * @param FinalizedPropertyDefinition[] $properties
     * @param PropertyUnsetter              $propertyUnsetter
     */
    public function __construct(
            \ReflectionClass $reflection,
            TypedObject $cleanInstance = null,
            array $properties,
            PropertyUnsetter $propertyUnsetter
    ) {
        Exception\InvalidArgumentException::verifyAllInstanceOf(
                __METHOD__,
                'properties',
                $properties,
                FinalizedPropertyDefinition::class
        );

        $this->isAbstract       = $reflection->isAbstract();
        $this->className        = $reflection->getName();
        $this->cleanInstance    = $cleanInstance;
        $this->propertyUnsetter = $propertyUnsetter;

        foreach ($properties as $property) {
            $name                                  = $property->getName();
            $this->properties[$name]               = $property;
            $this->propertyTypeMap[$name]          = $property->getType();
            $this->propertyDefaultMap[$name]       = $property->getDefaultValue();
            $this->propertyAccessibilityMap[$name] = $property->getAccessibility();
        }

        if ($this->cleanInstance) {
            $this->cleanInstance->loadFinalizedClassDefinition($this);
        }
    }

    /**
     * @return TypedObject|null
     */
    public function getCleanInstance()
    {
        return $this->cleanInstance;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return TypedObject|null
     */
    public function newCleanInstance()
    {
        return $this->cleanInstance ? clone $this->cleanInstance : null;
    }

    /**
     * Unsets the properties of the supplied instance
     * to allow them to be handled via the __get and __set
     * magic methods.
     *
     * @param TypedObject $instance
     *
     * @return void
     */
    public function cleanInstance(TypedObject $instance)
    {
        $this->propertyUnsetter->unsetProperties($instance);
    }

    /**
     * @return FinalizedPropertyDefinition[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param string $name
     *
     * @return FinalizedPropertyDefinition
     * @throws Exception\InvalidArgumentException
     */
    public function getProperty($name)
    {
        if (!isset($this->properties[$name])) {
            throw Exception\InvalidArgumentException::format(
                    'Invalid property name for class %s: expecting one of (%s), %s given',
                    $this->className, Debug::formatValues(array_keys($this->properties)), var_export($name, true)
            );
        }

        return $this->properties[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        return isset($this->propertyTypeMap[$name]);
    }

    /**
     * @return IType[]
     */
    public function getPropertyTypeMap()
    {
        return $this->propertyTypeMap;
    }

    /**
     * Gets the property type or null if the property is not defined.
     *
     * @param string $name
     *
     * @return IType|null
     */
    public function getPropertyType($name)
    {
        return isset($this->propertyTypeMap[$name]) ? $this->propertyTypeMap[$name] : null;
    }

    /**
     * @return array
     */
    public function getPropertyDefaultMap()
    {
        return $this->propertyDefaultMap;
    }

    /**
     * @return PropertyAccessibility[]
     */
    public function getPropertyAccessibilityMap()
    {
        return $this->propertyAccessibilityMap;
    }

    /**
     * Gets the property accessibility
     *
     * @param string $property
     *
     * @return PropertyAccessibility|null
     */
    public function getAccessibility($property)
    {
        return isset($this->propertyAccessibilityMap[$property])
                ? $this->propertyAccessibilityMap[$property]
                : null;
    }

    /**
     * Returns whether the supplied property is accessible
     * from the supplied class scope.
     *
     * @param string      $property
     * @param string|null $class
     *
     * @return bool
     */
    public function isAccessibleFrom($property, $class)
    {
        return isset($this->propertyAccessibilityMap[$property])
                ? $this->propertyAccessibilityMap[$property]->isAccessibleFrom($class)
                : false;
    }

    /**
     * Returns whether the class is abstract.
     *
     * @return bool
     */
    public function isAbstract()
    {
        return $this->isAbstract;
    }
}