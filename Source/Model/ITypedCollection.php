<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The typed collection interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ITypedCollection extends \IteratorAggregate
{
    /**
     * Gets the type of the elements of the collection.
     *
     * @return IType
     */
    public function getElementType();
}
