<?php

namespace Dms\Core\Module;

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
    public function getName();

    /**
     * Gets the label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Gets whether this is the default chart view.
     *
     * @return bool
     */
    public function isDefault();

    /**
     * Gets whether the view contains criteria.
     *
     * @return bool
     */
    public function hasCriteria();

    /**
     * Gets the chart criteria.
     *
     * @return IChartCriteria|null
     */
    public function getCriteria();
}