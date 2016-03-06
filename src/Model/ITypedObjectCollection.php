<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;

/**
 * The typed object collection interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ITypedObjectCollection extends ITypedCollection, IObjectSetWithLoadCriteriaSupport
{
    /**
     * Moves the object to the new position in the collection.
     *
     * @param ITypedObject $object
     * @param int          $newPosition 1-based
     *
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function move(ITypedObject $object, int $newPosition);

    /**
     * Moves the object at the supplied index to the new position in the collection.
     *
     * @param $index
     * @param int $newPosition 1-based
     *
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function moveAtIndex($index, int $newPosition);
}
