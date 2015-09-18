<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\IImmutableTypedObject;

/**
 * The fluent class definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ClassDefinition
{
    /**
     * @var int
     */
    private $propertyDefinitionTraceLevel = 1;

    /**
     * @var string
     */
    private $class;

    /**
     * @var object
     */
    private $instance;

    /**
     * @var PropertyDefinition[]
     */
    private $properties = [];

    /**
     * @var array
     */
    private $propertyReferenceMap = [];
    /**
     * @var \ReflectionClass
     */
    private $reflection;

    public function __construct(TypedObject $definitionInstance, \ReflectionClass $reflection, $baseObjectClass = TypedObject::class)
    {
        $this->reflection = $reflection;
        $this->class      = $reflection->getName();
        $this->instance   = $definitionInstance;

        while ($reflection->getName() !== $baseObjectClass) {
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {
                if (!$property->isStatic() && $property->getDeclaringClass() == $reflection) {
                    $this->loadPropertyDefinition($definitionInstance, $property);
                }
            }

            $reflection = $reflection->getParentClass();
        }
    }

    /**
     * Gets the class name of the class which is being defined.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param int $propertyDefinitionTraceLevel
     */
    public function setPropertyDefinitionTraceLevel($propertyDefinitionTraceLevel)
    {
        $this->propertyDefinitionTraceLevel = $propertyDefinitionTraceLevel;
    }

    private function loadPropertyDefinition($object, \ReflectionProperty $property)
    {
        $name  = $property->getName();
        $class = $property->getDeclaringClass()->getName();
        if (isset($this->properties[$name])) {
            throw new ConflictingPropertyNameException(
                    get_class($object),
                    $this->properties[$name]->getClass(),
                    $class,
                    $name
            );
        }

        $definition = new PropertyDefinition(
                $class,
                $name,
                PropertyAccessibility::from($property)
        );

        if (is_subclass_of($this->class, IImmutableTypedObject::class, true)) {
            $definition->setImmutable(true);
        }

        $this->properties[$name]           = $definition;
        $this->propertyReferenceMap[$name] =& $definition->getReferenceOn($object);
    }

    /**
     * Defines supplied the property.
     *
     * @param mixed $property
     *
     * @return PropertyTypeDefiner
     * @throws InvalidPropertyDefinitionException
     * @throws DuplicatePropertyDefinitionException
     */
    public function property(&$property)
    {
        $definition = $this->findPropertyFromReference($property);

        if ($definition->hasType()) {
            $traceLevel = $this->propertyDefinitionTraceLevel;
            $trace      = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $traceLevel)[$traceLevel - 1];
            throw new DuplicatePropertyDefinitionException($this->class, $definition->getName(), $trace['file'], $trace['line']);
        }

        return new PropertyTypeDefiner($definition);
    }

    /**
     * @param mixed $property
     *
     * @return PropertyDefinition
     * @throws InvalidPropertyDefinitionException
     */
    private function findPropertyFromReference(&$property)
    {
        $traceLevel = $this->propertyDefinitionTraceLevel;

        foreach ($this->propertyReferenceMap as $name => &$propertyReference) {
            if (ReferenceComparer::areEqual($property, $propertyReference)) {

                return $this->properties[$name];
            }
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $traceLevel + 1)[$traceLevel];
        throw new InvalidPropertyDefinitionException($this->class, $trace['file'], $trace['line']);
    }

    /**
     * Builds a finalized class definition object.
     *
     * @return FinalizedClassDefinition
     * @throws IncompleteClassDefinitionException
     */
    public function finalize()
    {
        foreach ($this->properties as $definition) {
            if (!$definition->hasType() && !$definition->isIgnored()) {
                throw new IncompleteClassDefinitionException($this->class, $definition);
            }
        }

        $properties               = [];
        $propertiesGroupedByClass = [];
        $defaultInstance          = clone $this->instance;

        foreach ($this->properties as $name => $definition) {
            if (!$definition->isIgnored()) {
                $properties[] = new FinalizedPropertyDefinition(
                        $definition->getName(),
                        $definition->getType(),
                        $definition->getReferenceOn($defaultInstance),
                        $definition->getAccessibility(),
                        $definition->isImmutable()
                );

                $propertiesGroupedByClass[$definition->getClass()][] = $definition->getName();
            }
        }

        /** @var TypedObject $cleanInstance */
        $cleanInstance = $this->reflection->isAbstract() ? null : clone $this->instance;

        return new FinalizedClassDefinition(
                $this->reflection,
                $cleanInstance,
                $properties,
                new PropertyUnsetter($propertiesGroupedByClass)
        );
    }
}