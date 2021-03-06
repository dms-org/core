<?php

declare(strict_types=1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;
use Dms\Core\Model\IImmutableTypedObject;
use Dms\Core\Model\Type\Builder\Type;
use PHPUnit\Framework\MockObject\MockObject;

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

    /**
     * @var bool
     */
    private $forceImmutability;

    public function __construct(
        TypedObject $definitionInstance,
        \ReflectionClass $reflection,
        $baseObjectClass = TypedObject::class,
        bool $forceImmutability = false
    ) {
        $this->reflection        = $reflection;
        $this->class             = $reflection->getName();
        $this->instance          = $definitionInstance;
        $this->forceImmutability = $forceImmutability;

        // Loads the class properties in the order of parent-most class to subclass
        $classes = [];

        do {
            $classes[]  = $reflection;
            $reflection = $reflection->getParentClass();
        } while ($reflection->getName() !== $baseObjectClass);

        foreach (array_reverse($classes) as $class) {
            /** @var \ReflectionClass $class */
            $properties = $class->getProperties();

            foreach ($properties as $property) {
                if (!$property->isStatic() && $property->getDeclaringClass() == $class) {
                    $this->loadPropertyDefinition($definitionInstance, $property);
                }
            }
        }
    }

    /**
     * Gets the class name of the class which is being defined.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param int $propertyDefinitionTraceLevel
     */
    public function setPropertyDefinitionTraceLevel(int $propertyDefinitionTraceLevel)
    {
        $this->propertyDefinitionTraceLevel = $propertyDefinitionTraceLevel;
    }

    private function loadPropertyDefinition($object, \ReflectionProperty $property)
    {
        $name  = $property->getName();
        $class = $property->getDeclaringClass()->getName();

        if (isset($this->properties[$name]) && $this->properties[$name]->getAccessibility()->isPrivate()) {
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
            PropertyAccessibility::from($property),
            $property
        );

        if ($this->forceImmutability) {
            $definition->setImmutable(true);
        }

        $this->properties[$name]           = $definition;

        if ($definition->canGetReference()) {
            $this->propertyReferenceMap[$name] = &$definition->getReferenceOn($object);
        }
    }

    /**
     * Defines supplied the property.
     *
     * @param mixed $property
     *
     * @return PropertyTypeDefiner
     * @throws InvalidPropertyDefinitionException
     */
    public function property(&$property): PropertyTypeDefiner
    {
        $definition = $this->findPropertyFromReference($property);

        return new PropertyTypeDefiner($definition);
    }

    /**
     * @param mixed $property
     *
     * @return PropertyDefinition
     * @throws InvalidPropertyDefinitionException
     */
    private function findPropertyFromReference(&$property): PropertyDefinition
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
     * @return void
     */
    private function inferPropertyTypesFromTypeDeclarations(): void
    {
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            return;
        }

        foreach ($this->properties as $definition) {
            if ($definition->hasType() || $definition->isIgnored()) {
                continue;
            }

            $reflection = $definition->getReflection();

            if (!$reflection->hasType()) {
                continue;
            }

            $definition->setType(Type::fromReflection($reflection->getType()));
        }
    }

    /**
     * Builds a finalized class definition object.
     *
     * @return FinalizedClassDefinition
     * @throws IncompleteClassDefinitionException
     */
    public function finalize(): FinalizedClassDefinition
    {
        // Automatically ignore any properties generated by phpunit mock objects
        // this allows typed objects to be used as mocks.
        if (is_a($this->class, MockObject::class, true)) {
            foreach ($this->properties as $definition) {
                if (strpos($definition->getName(), '__phpunit') === 0) {
                    $definition->setIgnored(true);
                }
            }
        }

        $this->inferPropertyTypesFromTypeDeclarations();

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
                    $definition->canGetReference() 
                        ? $definition->getReferenceOn($defaultInstance)
                        : (method_exists(\ReflectionProperty::class, 'getDefaultValue') ? $definition->getReflection()->getDefaultValue() : null),
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
