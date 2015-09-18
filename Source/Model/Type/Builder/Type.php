<?php

namespace Iddigital\Cms\Core\Model\Type\Builder;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedCollection;
use Iddigital\Cms\Core\Model\Type\ArrayType;
use Iddigital\Cms\Core\Model\Type\CollectionType;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\Type\MixedType;
use Iddigital\Cms\Core\Model\Type\NullType;
use Iddigital\Cms\Core\Model\Type\ObjectType;
use Iddigital\Cms\Core\Model\Type\ScalarType;
use Iddigital\Cms\Core\Model\Type\UnionType;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The type builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Type
{
    /**
     * @var MixedType
     */
    private static $mixed;

    /**
     * @var ScalarType[]
     */
    private static $scalars = [];

    /**
     * @var NullType
     */
    private static $null;

    /**
     * @var UnionType
     */
    private static $number;

    /**
     * @var ObjectType[]
     */
    private static $objects = [];

    /**
     * @var ArrayType[]
     */
    private static $arrays = [];

    /**
     * @var CollectionType[]
     */
    private static $collections = [];

    /**
     * @return MixedType
     */
    public static function mixed()
    {
        if (!self::$mixed) {
            self::$mixed = new MixedType();
        }

        return self::$mixed;
    }

    /**
     * @return ScalarType
     */
    public static function string()
    {
        return self::scalar(IType::STRING);
    }

    /**
     * @return ScalarType
     */
    public static function int()
    {
        return self::scalar(IType::INT);
    }

    /**
     * @return ScalarType
     */
    public static function bool()
    {
        return self::scalar(IType::BOOL);
    }

    /**
     * @return ScalarType
     */
    public static function float()
    {
        return self::scalar(IType::FLOAT);
    }

    /**
     * @return UnionType
     */
    public static function number()
    {
        if (!self::$number) {
            self::$number = UnionType::create([self::scalar(IType::INT), self::scalar(IType::FLOAT)]);
        }

        return self::$number;
    }

    /**
     * @return ScalarType
     */
    public static function null()
    {
        if (!self::$null) {
            self::$null = new NullType();
        }

        return self::$null;
    }

    /**
     * @param string $scalarType
     *
     * @return ScalarType
     */
    protected static function scalar($scalarType)
    {
        if (!isset(self::$scalars[$scalarType])) {
            self::$scalars[$scalarType] = new ScalarType($scalarType);
        }

        return self::$scalars[$scalarType];
    }

    /**
     * @param string|null $class
     *
     * @return ObjectType
     */
    public static function object($class = null)
    {
        if (!isset(self::$objects[$class])) {
            self::$objects[$class] = new ObjectType($class);
        }

        return self::$objects[$class];
    }

    /**
     * @param IType $elementType
     *
     * @return ArrayType
     */
    public static function arrayOf(IType $elementType)
    {
        $elementTypeString = $elementType->asTypeString();

        if (!isset(self::$arrays[$elementTypeString])) {
            self::$arrays[$elementTypeString] = new ArrayType($elementType);
        }

        return self::$arrays[$elementTypeString];
    }

    /**
     * @param IType $elementType
     *
     * @return CollectionType
     */
    public static function collectionOf(IType $elementType)
    {
        $elementTypeString = $elementType->asTypeString();

        if (!isset(self::$collections[$elementTypeString])) {
            self::$collections[$elementTypeString] = new CollectionType($elementType);
        }

        return self::$collections[$elementTypeString];
    }

    /**
     * @param mixed $default
     *
     * @return IType
     * @throws InvalidArgumentException
     */
    public static function from($default)
    {
        switch (gettype($default)) {
            case 'NULL':
                return self::null();
            case 'string':
                return self::string();
            case 'integer':
                return self::int();
            case 'double':
                return self::float();
            case 'boolean':
                return self::bool();
            case 'array':
                return self::arrayOf(self::mixed());
            case 'object':
                if ($default instanceof ITypedCollection) {
                    return self::collectionOf($default->getElementType());
                } else {
                    return self::object(get_class($default));
                }
        }

        throw InvalidArgumentException::format(
                'Unknown type: %s', Debug::getType($default)
        );
    }
}