<?php declare(strict_types = 1);

namespace Dms\Core\Module\Chart;

use Dms\Core\Module\IChartView;
use Dms\Core\Table\Chart\Criteria\ChartCriteria;
use Dms\Core\Table\Chart\IChartCriteria;
use Dms\Core\Table\IRowCriteria;

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
    public function __construct(string $name, string $label, bool $default, IChartCriteria $criteria = null)
    {
        $this->name     = $name;
        $this->label    = $label;
        $this->default  = $default;
        $this->criteria = $criteria;
    }

    /**
     * @return ChartView
     */
    public static function createDefault() : ChartView
    {
        return new self('default', 'Default', true);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @return boolean
     */
    public function isDefault() : bool
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function hasCriteria() : bool
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

    /**
     * @inheritDoc
     */
    public function getCriteriaCopy()
    {
        return $this->criteria ? $this->criteria->asNewCriteria() : null;
    }
}