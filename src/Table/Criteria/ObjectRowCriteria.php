<?php declare(strict_types = 1);

namespace Dms\Core\Table\Criteria;

use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Table\IObjectRowCriteria;
use Dms\Core\Table\ITableStructure;

/**
 * The row search criteria interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ObjectRowCriteria extends RowCriteria implements IObjectRowCriteria
{
    /**
     * @var IObjectSet
     */
    protected $objectSet;

    /**
     * @var Criteria
     */
    protected $objectCriteria;

    public function __construct(ITableStructure $structure, IObjectSet $objectSet)
    {
        parent::__construct($structure);
        $this->objectSet      = $objectSet;
        $this->objectCriteria = $objectSet->criteria();
    }

    /**
     * @return IObjectSet
     */
    public function getObjectSet(): IObjectSet
    {
        return $this->objectSet;
    }

    /**
     * @return Criteria
     */
    public function getObjectCriteria(): Criteria
    {
        return $this->objectCriteria;
    }

    /**
     * @param callable $objectCriteriaCallback
     *
     * @return self
     */
    public function matches(callable $objectCriteriaCallback)
    {
        $objectCriteriaCallback($this->objectCriteria);

        return $this;
    }
}
