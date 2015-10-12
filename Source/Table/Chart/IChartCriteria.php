<?php

namespace Iddigital\Cms\Core\Table\Chart;

use Iddigital\Cms\Core\Table\Chart\Criteria\AxisCondition;
use Iddigital\Cms\Core\Table\Chart\Criteria\AxisOrdering;

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
}