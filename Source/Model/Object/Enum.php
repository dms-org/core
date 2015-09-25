<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The enum object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Enum extends TypedObject
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
    final public static function getEnumType()
    {
        return static::definition()->getPropertyType('value');
    }

    /**
     * Returns the array of valid enum options.
     *
     * @return array
     */
    final public static function getOptions()
    {
        return static::loadEnumConstants();
    }

    /**
     * Returns whether the supplied enum is a valid option
     * for the called enum class.
     *
     * @param string $value
     *
     * @return bool
     */
    final public static function isValid($value)
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
    final public function is($value)
    {
        return $this == $value || $this->value === $value;
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
}