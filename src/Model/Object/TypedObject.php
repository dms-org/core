<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\Criteria\CustomSpecification;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\ObjectCollection;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\CollectionType;
use Dms\Core\Model\Type\IType;
use Dms\Core\Model\Type\ObjectType;

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
    private $properties;

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
    final public static function definition() : FinalizedClassDefinition
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

            $definition         = new ClassDefinition($definitionInstance, $reflection, __CLASS__);
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
    public static function criteria() : Criteria
    {
        return new Criteria(static::definition());
    }

    /**
     * Returns the type of the called class.
     *
     * @return ObjectType
     */
    final public static function type() : ObjectType
    {
        return Type::object(get_called_class());
    }

    /**
     * Creates a specification for the current class according to the
     * supplied callback.
     *
     * Example:
     * <code>
     * self::specification(function (SpecificationDefinition $match) {
     *      $match->where(self::PROPERTY, '=', 'some-value');
     * });
     * </code>
     *
     * @see SpecificationDefinition
     *
     * @param callable $definitionCallback
     *
     * @return ISpecification
     */
    final protected static function specification(callable $definitionCallback) : ISpecification
    {
        return new CustomSpecification(get_called_class(), $definitionCallback);
    }

    /**
     * Returns a typed collection with the element type as
     * the called class.
     *
     * @param static[] $objects
     *
     * @return ObjectCollection|static[]
     */
    public static function collection(array $objects = [])
    {
        return new ObjectCollection(get_called_class(), $objects);
    }

    /**
     * Returns the type of the collection for this typed object.
     *
     * @return CollectionType
     */
    public static function collectionType() : CollectionType
    {
        return Type::collectionOf(Type::object(get_called_class()), ObjectCollection::class);
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
    final public function getClassDefinition() : FinalizedClassDefinition
    {
        return $this->definition;
    }

    /**
     * {@inheritDoc}
     */
    final public function toArray() : array
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
    final public function serialize()
    {
        return serialize($this->dataToSerialize());
    }

    /**
     * Gets the data to be serialized when the class is serialized.
     *
     * @return mixed
     */
    protected function dataToSerialize()
    {
        return $this->properties;
    }

    /**
     * @inheritDoc
     */
    final public function unserialize($serialized)
    {
        $this->loadFinalizedClassDefinition(static::definition());
        $this->hydrateFromSerializedData(unserialize($serialized));
    }

    /**
     * Gets the data to be serialized when the class is serialized.
     *
     * @param $deserializedData
     *
     * @return void
     */
    protected function hydrateFromSerializedData($deserializedData)
    {
        $this->properties = $deserializedData;
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

        if (TypedObjectAccessibilityAssertion::isEnabled() && !$property->getAccessibility()->isPublic()) {
            $this->validateAccessibility($name, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
        }

        return $this->properties[$name];
    }

    final public function __set($name, $value)
    {
        $this->validateProperty($name);

        $property = $this->definition->getProperty($name);

        if (TypedObjectAccessibilityAssertion::isEnabled() && !$property->getAccessibility()->isPublic()) {
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
}