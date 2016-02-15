<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;
use Dms\Core\Model\Type\IType;
use Dms\Core\Util\Hashing\IHashable;

/**
 * The enum object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Enum extends TypedObject implements IHashable
{
    /**
     * @var array
     */
    private static $constants = [];

    /**
     * @var mixed
     */
    private $value;

    final public function __construct($value)
    {
        if (!static::isValid($value)) {
            throw new InvalidEnumValueException(get_class($this), static::getOptions(), $value);
        }

        parent::__construct();
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    final protected function define(ClassDefinition $class)
    {
        $this->defineEnumValues($class->property($this->value));
    }

    /**
     * Defines the type of the options contained within the enum.
     *
     * @param PropertyTypeDefiner $values
     *
     * @return void
     */
    protected abstract function defineEnumValues(PropertyTypeDefiner $values);

    /**
     * @inheritDoc
     */
    protected function dataToSerialize()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    protected function hydrateFromSerializedData($deserializedData)
    {
        $this->value = $deserializedData;
    }

    /**
     * @inheritDoc
     */
    final public function getObjectHash() : string
    {
        return is_scalar($this->value)
                ? (string)$this->value
                : serialize($this->value);
    }

    private static function loadEnumConstants()
    {
        $class = get_called_class();

        if (!isset(self::$constants[$class])) {
            $constants = (new \ReflectionClass($class))->getConstants();

            $enumType = static::getEnumType();
            foreach ($constants as $value) {
                if (!$enumType->isOfType($value)) {
                    throw new InvalidEnumValueException($class, $constants, $value);
                }
            }

            self::$constants[$class] = $constants;
        }

        return self::$constants[$class];
    }

    /**
     * @return IType
     */
    final public static function getEnumType() : \Dms\Core\Model\Type\IType
    {
        return static::definition()->getPropertyType('value');
    }

    /**
     * Returns the array of valid enum options.
     *
     * @return array
     */
    final public static function getOptions() : array
    {
        return static::loadEnumConstants();
    }

    /**
     * Gets all the possible enums as an array.
     *
     * @return static[]
     */
    final public static function getAll() : array
    {
        $enums = [];

        foreach (static::getOptions() as $option) {
            $enums[] = new static($option);
        }

        return $enums;
    }

    /**
     * Returns whether the supplied enum is a valid option
     * for the called enum class.
     *
     * @param mixed $value
     *
     * @return bool
     */
    final public static function isValid($value) : bool
    {
        $constants = static::loadEnumConstants();

        return in_array($value, $constants, true);
    }

    /**
     * Gets the value represented by the current
     * enum instance.
     *
     * @return mixed
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns whether the enum is equivalent to the supplied
     * enum or if it has the supplied enum value.
     *
     * @param Enum|mixed $value
     *
     * @return bool
     */
    final public function is($value) : bool
    {
        return $this == $value || $this->value === $value;
    }
}