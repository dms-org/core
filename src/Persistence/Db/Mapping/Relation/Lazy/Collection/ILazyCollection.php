<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Dms\Core\Model\ITypedCollection;
use Pinq\Iterators\Generators\GeneratorScheme;
use Traversable;

/**
 * The lazy collection interface.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ILazyCollection extends ITypedCollection
{
    /**
     * Returns whether the collection has been loaded.
     * 
     * @return bool
     */
    public function hasLoadedElements() : bool;    
}