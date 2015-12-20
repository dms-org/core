<?php

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Type\IType;

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
