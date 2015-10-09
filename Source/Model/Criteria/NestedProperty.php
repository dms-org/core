<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Object\TypedObjectAccessibilityAssertion;
use Iddigital\Cms\Core\Model\Type\ObjectType;

/**
 * The property criterion base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NestedProperty
{
    /**
     * @var FinalizedPropertyDefinition[]
     */
    protected $properties;

    /**
     * @var callable
     */
    protected $getter;

    /**
     * PropertyCriterion constructor.
     *
     * @param FinalizedPropertyDefinition[] $properties
     */
    public function __construct(array $properties)
    {
        InvalidArgumentException::verify(!empty($properties), 'properties cannot be empty');
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'properties', $properties, FinalizedPropertyDefinition::class);

        $this->properties = $properties;
        $this->getter     = $this->makePropertyGetterCallable();
    }

    /**
     * Parses a nested property name using '.' notation
     *
     * Example:
     * <code>
     * 'some.nested.property'
     * </code>
     *
     * @param FinalizedClassDefinition $class
     * @param string                   $propertyName
     *
     * @return NestedProperty
     * @throws InvalidArgumentException
     * @throws InvalidOperationException
     */
    public static function parsePropertyName(FinalizedClassDefinition $class, $propertyName)
    {
        $parts      = explode('.', $propertyName);
        $partsCount = count($parts);

        $properties = [];
        $nullable   = false;

        foreach ($parts as $key => $propertyPart) {
            $property = $class->getProperty($propertyPart);
            if ($nullable) {
                $property = $property->asNullable();
            }

            $properties[] = $property;

            if ($key < $partsCount - 1) {
                if ($property->getType()->isNullable()) {
                    $nullable = true;
                }

                $type = $property->getType()->nonNullable();

                if (!($type instanceof ObjectType) || !is_subclass_of($type->getClass(), TypedObject::class, true)) {
                    throw InvalidOperationException::format(
                            'Invalid property string \'%s\': property %s::$%s must be a subclass of %s to use nested property, %s given',
                            $propertyName, $class->getClassName(), $propertyPart, TypedObject::class, $type->asTypeString()
                    );
                }

                /** @var TypedObject|string $className */
                $className = $type->getClass();
                $class     = $className::definition();
            }
        }

        return new self($properties);
    }

    /**
     * @return FinalizedPropertyDefinition[]
     */
    final public function getNestedProperties()
    {
        return $this->properties;
    }

    /**
     * @return \Closure
     */
    final public function makePropertyGetterCallable()
    {
        $properties = $this->properties;

        $getter = function (ITypedObject $object) {
            return $object;
        };

        foreach ($properties as $property) {
            $name   = $property->getName();
            $getter = $this->ensureAccessible(
                    function (ITypedObject $object) use ($getter, $name) {
                        $object = $getter($object);

                        return $object === null ? null : $object->{$name};
                    },
                    $property
            );
        }

        return $getter;
    }

    /**
     * @param \Closure                    $closure
     * @param FinalizedPropertyDefinition $property
     *
     * @return \Closure
     */
    final protected function ensureAccessible(\Closure $closure, FinalizedPropertyDefinition $property)
    {
        return \Closure::bind($closure, null, $property->getAccessibility()->getDeclaredClass());
    }
}