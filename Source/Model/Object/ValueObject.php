<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\IValueObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\ValueObjectCollection;
use Iddigital\Cms\Core\Util\Hashing\IHashable;

/**
 * The value object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ValueObject extends TypedObject implements IValueObject, IHashable
{
    /**
     * Returns a value object collection with the element type
     * as the called class.
     *
     * @param static[] $objects
     *
     * @return ValueObjectCollection|static[]
     */
    public static function collection(array $objects = [])
    {
        return new ValueObjectCollection(get_called_class(), $objects);
    }

    /**
     * Returns the type of the collection for this typed object.
     *
     * @return IType
     */
    public static function collectionType()
    {
        return Type::collectionOf(Type::object(get_called_class()), ValueObjectCollection::class);
    }

    /**
     * @inheritDoc
     */
    public function getObjectHash()
    {
        return serialize($this->toArray());
    }
}