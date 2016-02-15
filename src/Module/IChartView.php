<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Table\Chart\Criteria\ChartCriteria;
use Dms\Core\Table\Chart\IChartCriteria;

/**
 * The chart view interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IChartView
{
    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets the label.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Gets whether this is the default chart view.
     *
     * @return bool
     */
    public function isDefault() : bool;

    /**
     * Gets whether the view contains criteria.
     *
     * @return bool
     */
    public function hasCriteria() : bool;

    /**
     * Gets the chart criteria.
     *
     * @return IChartCriteria|null
     */
    public function getCriteria();

    /**
     * Gets a copy of the chart criteria or null if there is no criteria.
     *
     * @return ChartCriteria|null
     */
    public function getCriteriaCopy();
}