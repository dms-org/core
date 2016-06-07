<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\EntityIdCollection;
use Pinq\Iterators\IIteratorScheme;

/**
 * The lazy loaded entity id collection
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyEntityIdCollection extends EntityIdCollection
{
    use LazyCollectionTrait;

    /**
     * @param callable             $entityIdLoaderCallback
     * @param IIteratorScheme|null $scheme
     */
    public function __construct(
            callable $entityIdLoaderCallback,
            IIteratorScheme $scheme = null
    ) {
        parent::__construct([], $scheme);

        $this->setLazyLoadingCallback($entityIdLoaderCallback);
    }
}