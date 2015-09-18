<?php

namespace Iddigital\Cms\Core\Table;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Table\Criteria\ColumnCondition;
use Iddigital\Cms\Core\Table\Criteria\ColumnGrouping;
use Iddigital\Cms\Core\Table\Criteria\ColumnOrdering;

/**
 * The row search criteria interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IRowCriteria
{
    /**
     * Gets the table structure interface.
     *
     * @return ITableStructure
     */
    public function getStructure();

    /**
     * Gets the conditions which the rows must match
     * to load the rows.
     *
     * @return ColumnCondition[]
     */
    public function getConditions();

    /**
     * Gets the order in which to load the rows
     *
     * @return ColumnOrdering[]
     */
    public function getOrderings();

    /**
     * Gets the groupings which will group the rows into sections.
     *
     * @return ColumnGrouping[]
     */
    public function getGroupings();

    /**
     * Gets the starting row offset.
     *
     * @return int
     */
    public function getRowsToSkip();

    /**
     * Gets the amount of rows to load.
     *
     * @return int|null
     */
    public function getAmountOfRows();
}
