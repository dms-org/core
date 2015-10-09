<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\PartialLoadCriteria;

/**
 * The object set interface that also supports loading partial objects in the form of arrays.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IObjectSetWithPartialLoadSupport extends IObjectSet
{
    /**
     * Creates a criteria for the object class which can also
     * define which properties of the objects to load.
     *
     * @return PartialLoadCriteria
     * @throws Exception\TypeMismatchException
     */
    public function partialCriteria();

    /**
     * Returns an array of arrays containing the properties of the
     * object matching the supplied criteria.
     *
     * @param IPartialLoadCriteria $criteria
     *
     * @return ITypedObject[]
     * @throws Exception\TypeMismatchException
     */
    public function loadPartial(IPartialLoadCriteria $criteria);
}