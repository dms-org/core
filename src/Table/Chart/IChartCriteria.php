<?php

namespace Dms\Core\Table\Chart;

use Dms\Core\Table\Chart\Criteria\AxisCondition;
use Dms\Core\Table\Chart\Criteria\AxisOrdering;
use Dms\Core\Table\Chart\Criteria\ChartCriteria;

/**
 * The chart criteria interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IChartCriteria
{
    /**
     * Gets the table structure interface.
     *
     * @return IChartStructure
     */
    public function getStructure();

    /**
     * Gets the conditions which the rows must match
     * to load the rows.
     *
     * @return AxisCondition[]
     */
    public function getConditions();

    /**
     * Gets the order in which to load the rows
     *
     * @return AxisOrdering[]
     */
    public function getOrderings();

    /**
     * Returns a copy of the criteria.
     *
     * @return ChartCriteria
     */
    public function asNewCriteria();
}