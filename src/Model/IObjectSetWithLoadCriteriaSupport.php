<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\LoadCriteria;

/**
 * The object set interface that also supports loading partial objects in the form of arrays.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IObjectSetWithLoadCriteriaSupport extends IObjectSet
{
    /**
     * Creates a criteria for the object class which can also
     * define the data from the objects of which to load.
     *
     * @return LoadCriteria
     * @throws Exception\TypeMismatchException
     */
    public function loadCriteria() : Criteria\LoadCriteria;

    /**
     * Returns an array of arrays containing the properties of the
     * object matching the supplied criteria.
     *
     * @param ILoadCriteria $criteria
     *
     * @return array[]
     * @throws Exception\TypeMismatchException
     */
    public function loadMatching(ILoadCriteria $criteria) : array;
}