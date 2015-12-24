<?php

namespace Dms\Core\Table;

use Dms\Core\Exception;
use Dms\Core\Table\Criteria\ColumnCondition;
use Dms\Core\Table\Criteria\ColumnGrouping;
use Dms\Core\Table\Criteria\ColumnOrdering;
use Dms\Core\Table\Criteria\RowCriteria;

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
     * Gets the table columns to load.
     *
     * @return IColumn[]
     */
    public function getColumnsToLoad();

    /**
     * Gets the table column names to load.
     *
     * @return string[]
     */
    public function getColumnNamesToLoad();

    /**
     * Returns whether all columns are loaded.
     *
     * @return bool
     */
    public function getWhetherLoadsAllColumns();

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

    /**
     * Returns a copy of the criteria.
     *
     * @return RowCriteria
     */
    public function asNewCriteria();
}
