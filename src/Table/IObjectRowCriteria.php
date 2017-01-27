<?php declare(strict_types = 1);

namespace Dms\Core\Table;

use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\IObjectSet;

/**
 * The object row search criteria interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IObjectRowCriteria extends IRowCriteria
{
    /**
     * @return IObjectSet
     */
    public function getObjectSet(): IObjectSet;

    /**
     * @return Criteria
     */
    public function getObjectCriteria();
}
