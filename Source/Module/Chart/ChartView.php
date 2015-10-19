<?php

namespace Iddigital\Cms\Core\Module\Chart;

use Iddigital\Cms\Core\Module\IChartView;
use Iddigital\Cms\Core\Table\Chart\IChartCriteria;
use Iddigital\Cms\Core\Table\IRowCriteria;

/**
 * The chart view class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartView implements IChartView
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $default;

    /**
     * @var IChartCriteria|null
     */
    protected $criteria;

    /**
     * TableView constructor.
     *
     * @param string            $name
     * @param string            $label
     * @param bool              $default
     * @param IChartCriteria|null $criteria
     */
    public function __construct($name, $label, $default, IChartCriteria $criteria = null)
    {
        $this->name     = $name;
        $this->label    = $label;
        $this->default  = $default;
        $this->criteria = $criteria;
    }

    /**
     * @return ChartView
     */
    public static function createDefault()
    {
        return new self('default', 'Default', true);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function hasCriteria()
    {
        return $this->criteria !== null;
    }

    /**
     * @return IChartCriteria|null
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}