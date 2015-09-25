<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\Criteria\Criteria;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Model\ITypedObjectCollection;
use Iddigital\Cms\Core\Model\ObjectCollection;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\ObjectType;

/**
 * The typed object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TypedObject implements ITypedObject, \Serializable
{
    /**
     * @var FinalizedClassDefinition[]
     */
    private static $definitions = [];

    /**
     * @var FinalizedClassDefinition
     */
    private $definition;

    /**
     * @var array
     */
    protected $properties;

    public function __construct()
    {
        $this->loadFinalizedClassDefinition(static::definition());
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    abstract protected function define(ClassDefinition $class);

    /**
     * Gets the class definition for the called class.
     *
     * @return FinalizedClassDefinition
     */
    final public static function definition()
    {
        $class = get_called_class();

        if (!isset(self::$definitions[$class])) {
            /** @var self $definitionInstance */
            $reflection = new \ReflectionClass($class);

            if ($reflection->isAbstract()) {
                $definitionInstance = AbstractProxyGenerator::createProxyInstance($class);
            } else {
                $definitionInstance = $reflection->newInstanceWithoutConstructor();
            }

            $definition = new ClassDefinition($definitionInstance, $reflection, __CLASS__);
            $overrideDefinition = $definitionInstance->define($definition);

            if ($overrideDefinition instanceof ClassDefinition) {
                $definition = $overrideDefinition->finalize();
            } elseif ($overrideDefinition instanceof FinalizedClassDefinition) {
                $definition = $overrideDefinition;
            } else {
                $definition = $definition->finalize();
            }

            self::$definitions[$class] = $definition;
        }

        return self::$definitions[$class];
    }

    /**
     * Returns a new criteria instance for the called class.
     *
     * @return Criteria
     */
    public static function criteria()
    {
        return new Criteria(static::definition());
    }

    /**
     * Returns the type of the called class.
     *
     * @return ObjectType
     */
    final public static function type()
    {
        return Type::object(get_called_class());
    }

    /**
     * Returns a typed collection with the element type as
     * the called class.
     *
     * @return ITypedObjectCollection|static[]
     */
    public static function collection()
    {
        return new ObjectCollection(get_called_class());
    }

    /**
     * Constructs a new instance of the class.
     *
     * @return static
     */
    final protected static function construct()
    {
        return self::definition()->newCleanInstance();
    }

    final public function loadFinalizedClassDefinition(FinalizedClassDefinition $definition)
    {
        $definition->cleanInstance($this);
        $this->definition = $definition;
        $this->properties = $definition->getPropertyDefaultMap();
    }

    /**
     * Gets the class definition
     *
     * @return FinalizedClassDefinition
     */
    final public function getClassDefinition()
    {
        return $this->definition;
    }

    /**
     * {@inheritDoc}
     */
    final public function toArray()
    {
        return $this->properties;
    }

    /**
     * {@inheritDoc}
     */
    final public function hydrate(array $properties)
    {
        $this->properties = $properties + $this->properties;
    }

    /**
     * Creates a new instance with the supplied properties.
     *
     * The property types and structure are NOT validated in any way
     * and as such this should only be used for restoring object
     * state from a persistence store which is in a valid state.
     *
     * @param array $properties
     *
     * @return static
     */
    final public static function hydrateNew(array $properties)
    {
        $object             = static::construct();
        $object->properties = $properties;

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize($this->properties);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $this->loadFinalizedClassDefinition(static::definition());
        $this->properties = unserialize($serialized);
    }

    private function validateProperty($name)
    {
        if (!$this->definition) {
            throw new InvalidOperationException('Definition has not been set yet, possibly due to access to dynamic property during class definition?');
        }

        if (!$this->definition->hasProperty($name)) {
            throw new UndefinedPropertyException(get_class($this), $name);
        }

        return $this->definition->getProperty($name);
    }

    private function validateAccessibility($name, array $trace)
    {
        $class = isset($trace[1]['class']) ? $trace[1]['class'] : null;
        if (!$this->definition->isAccessibleFrom($name, $class)) {
            throw new InaccessiblePropertyException(get_class($this), $name, $class);
        }
    }

    final public function __get($name)
    {
        $property = $this->validateProperty($name);

        if (!$property->getAccessibility()->isPublic()) {
            $this->validateAccessibility($name, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
        }

        return $this->properties[$name];
    }

    final public function __set($name, $value)
    {
        $this->validateProperty($name);

        $property = $this->definition->getProperty($name);

        if (!$property->getAccessibility()->isPublic()) {
            $this->validateAccessibility($name, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
        }

        $type = $property->getType();
        if ($type->isOfType($value)) {
            if ($property->isImmutable() && isset($this->properties[$name]) && $this->properties[$name] !== $value) {
                throw new ImmutablePropertyException(get_class($this), $name);
            }

            $this->properties[$name] = $value;
        } else {
            throw new InvalidPropertyValueException(get_class($this), $name, $type, $value);
        }
    }

    final public function __isset($name)
    {
        if (!$this->definition->hasProperty($name)) {
            return false;
        }

        if ($this->definition->getAccessibility($name)->isPublic()) {
            return true;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        return $this->definition->isAccessibleFrom($name, isset($trace[1]['class']) ? $trace[1]['class'] : null);
    }

    final public function __unset($name)
    {
        $class = get_class($this);
        throw new InvalidOperationException("Cannot unset(...->\${$name}): property deletion is disallowed on {$class}");
    }

    final public function __debugInfo()
    {
        return $this->properties;
    }
}