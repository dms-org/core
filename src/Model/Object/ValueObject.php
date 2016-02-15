<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;
use Dms\Core\Model\IValueObject;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;
use Dms\Core\Model\ValueObjectCollection;
use Dms\Core\Util\Hashing\IHashable;

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
    public static function collectionType() : \Dms\Core\Model\Type\IType
    {
        return Type::collectionOf(Type::object(get_called_class()), ValueObjectCollection::class);
    }

    /**
     * @inheritDoc
     */
    public function getObjectHash() : string
    {
        return serialize($this->dataToSerialize());
    }
}