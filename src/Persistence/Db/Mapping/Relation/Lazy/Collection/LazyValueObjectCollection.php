<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Dms\Core\Model\ValueObjectCollection;
use Pinq\Iterators\IIteratorScheme;

/**
 * The lazy loaded value object collection
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyValueObjectCollection extends ValueObjectCollection implements ILazyCollection
{
    use LazyCollectionTrait;

    /**
     * @param string               $valueObjectType
     * @param callable             $valueObjectCallback
     * @param IIteratorScheme|null $scheme
     */
    public function __construct(
            string $valueObjectType,
            callable $valueObjectCallback,
            IIteratorScheme $scheme = null
    ) {
        parent::__construct($valueObjectType, [], $scheme);

        $this->setLazyLoadingCallback($valueObjectCallback);
    }
}