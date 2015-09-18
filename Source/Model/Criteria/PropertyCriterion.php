<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;

/**
 * The property criterion base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyCriterion
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
        $this->getter = $this->makePropertyGetterCallable();
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
        $properties   = $this->properties;

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