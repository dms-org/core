<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\IValueObject;
use Iddigital\Cms\Core\Model\ValueObjectCollection;

/**
 * The value object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ValueObject extends TypedObject implements IValueObject
{
    /**
     * Returns a value object collection with the element type
     * as the called class.
     *
     * @param static[] $objects
     *
     * @return ValueObjectCollection|static[]
     */
    final public static function collection(array $objects = [])
    {
        return new ValueObjectCollection(get_called_class(), $objects);
    }
}