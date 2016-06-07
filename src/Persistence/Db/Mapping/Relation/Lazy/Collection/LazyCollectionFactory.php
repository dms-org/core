<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\TypedCollection;
use Dms\Core\Model\ValueObjectCollection;

/**
 * The lazy loaded entity object collection
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyCollectionFactory
{
    /**
     * @param ITypedCollection $collection
     * @param callable         $objectLoaderCallback
     *
     * @return ITypedCollection
     * @throws InvalidArgumentException
     */
    public static function from(ITypedCollection $collection, callable $objectLoaderCallback) : ITypedCollection
    {
        if ($collection instanceof EntityCollection) {
            return new LazyEntityCollection($collection->getEntityType(), $objectLoaderCallback);
        }

        if ($collection instanceof EntityIdCollection) {
            return new LazyEntityIdCollection($objectLoaderCallback);
        }

        if ($collection instanceof ValueObjectCollection) {
            return new LazyValueObjectCollection($collection->getObjectType(), $objectLoaderCallback);
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unsupported collection type %s given',
                __METHOD__, get_class($collection)
        );
    }
}