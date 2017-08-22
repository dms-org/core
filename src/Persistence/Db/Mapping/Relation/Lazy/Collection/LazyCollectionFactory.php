<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\ObjectCollection;
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
     * @return ILazyCollection
     * @throws InvalidArgumentException
     */
    public static function from(ITypedCollection $collection, callable $objectLoaderCallback) : ILazyCollection
    {
        if (method_exists($collection, 'createLazyCollection')) {
            return $collection->createLazyCollection($objectLoaderCallback);
        }

        if ($collection instanceof EntityCollection) {
            return new LazyEntityCollection($collection->getEntityType(), $objectLoaderCallback);
        }

        if ($collection instanceof EntityIdCollection) {
            return new LazyEntityIdCollection($objectLoaderCallback);
        }

        if ($collection instanceof ValueObjectCollection) {
            return new LazyValueObjectCollection($collection->getObjectType(), $objectLoaderCallback);
        }

        if ($collection instanceof ObjectCollection) {
            return new LazyObjectCollection($collection->getObjectType(), $objectLoaderCallback);
        }

        throw InvalidArgumentException::format(
            'Invalid call to %s: unsupported collection type %s given',
            __METHOD__, get_class($collection)
        );
    }
}