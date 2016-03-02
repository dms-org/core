<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;

/**
 * The value object collection interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IValueObjectCollection extends ITypedObjectCollection, IObjectSetWithIdentityByIndex, IMutableObjectSet
{

    /**
     * Updates the supplied object with the new object.
     *
     * @param IValueObject $object
     * @param IValueObject $newObject
     *
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function update(IValueObject $object, IValueObject $newObject);

    /**
     * Updates the supplied object at the supplied index with the new object.
     *
     * @param int          $index
     * @param IValueObject $newObject
     *
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function updateAtIndex(int $index, IValueObject $newObject);
}
