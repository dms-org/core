<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\LoadCriteria;

/**
 * The object set interface that also supports loading partial objects in the form of arrays.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IObjectSetWithLoadCriteriaSupport extends IObjectSet
{
    /**
     * Creates a criteria for the object class which can also
     * define which properties of the objects to load.
     *
     * @return LoadCriteria
     * @throws Exception\TypeMismatchException
     */
    public function loadCriteria();

    /**
     * Returns an array of arrays containing the properties of the
     * object matching the supplied criteria.
     *
     * @param ILoadCriteria $criteria
     *
     * @return array[]
     * @throws Exception\TypeMismatchException
     */
    public function loadPartial(ILoadCriteria $criteria);
}