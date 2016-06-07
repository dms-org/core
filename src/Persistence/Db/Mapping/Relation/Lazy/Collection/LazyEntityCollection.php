<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Dms\Core\Model\EntityCollection;
use Pinq\Iterators\IIteratorScheme;

/**
 * The lazy loaded entity object collection
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyEntityCollection extends EntityCollection implements ILazyCollection
{
    use LazyCollectionTrait;

    /**
     * @param string               $entityType
     * @param callable             $entityLoaderCallback
     * @param IIteratorScheme|null $scheme
     */
    public function __construct(
            string $entityType,
            callable $entityLoaderCallback,
            IIteratorScheme $scheme = null
    ) {
        parent::__construct($entityType, [], $scheme);

        $this->setLazyLoadingCallback($entityLoaderCallback);
    }
}