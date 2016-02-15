<?php declare(strict_types = 1);

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
    public function getStructure() : ITableStructure;

    /**
     * Gets the table columns to load.
     *
     * @return IColumn[]
     */
    public function getColumnsToLoad() : array;

    /**
     * Gets the table column names to load.
     *
     * @return string[]
     */
    public function getColumnNamesToLoad() : array;

    /**
     * Returns whether all columns are loaded.
     *
     * @return bool
     */
    public function getWhetherLoadsAllColumns() : bool;

    /**
     * Gets the conditions which the rows must match
     * to load the rows.
     *
     * @return ColumnCondition[]
     */
    public function getConditions() : array;

    /**
     * Gets the order in which to load the rows
     *
     * @return ColumnOrdering[]
     */
    public function getOrderings() : array;

    /**
     * Gets the groupings which will group the rows into sections.
     *
     * @return ColumnGrouping[]
     */
    public function getGroupings() : array;

    /**
     * Gets the starting row offset.
     *
     * @return int
     */
    public function getRowsToSkip() : int;

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
    public function asNewCriteria() : Criteria\RowCriteria;
}
