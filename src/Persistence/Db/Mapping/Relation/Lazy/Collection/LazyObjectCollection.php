<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Dms\Core\Model\ObjectCollection;
use Pinq\Iterators\IIteratorScheme;

/**
 * The lazy loaded object collection
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyObjectCollection extends ObjectCollection implements ILazyCollection
{
    use LazyCollectionTrait;

    /**
     * @param string               $objectType
     * @param callable             $objectLoaderCallback
     * @param IIteratorScheme|null $scheme
     */
    public function __construct(
        string $objectType,
        callable $objectLoaderCallback,
        IIteratorScheme $scheme = null
    ) {
        parent::__construct($objectType, [], $scheme);

        $this->setLazyLoadingCallback($objectLoaderCallback);
        $this->instanceMap = null;
    }
}