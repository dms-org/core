<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Table\Chart\IChartCriteria;
use Dms\Core\Table\Chart\IChartDataTable;

/**
 * The chart widget class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartWidget extends Widget
{
    /**
     * @var IChartDisplay
     */
    protected $chartDisplay;

    /**
     * @var IChartCriteria|null
     */
    protected $criteria;

    /**
     * @inheritDoc
     */
    public function __construct(string $name, string $label, IAuthSystem $authSystem, array $requiredPermissions, IChartDisplay $chartDisplay, IChartCriteria $criteria = null)
    {
        parent::__construct($name, $label, $authSystem, $requiredPermissions);
        $this->chartDisplay = $chartDisplay;
        $this->criteria     = $criteria;
    }

    /**
     * @return IChartDisplay
     */
    public function getChartDisplay() : IChartDisplay
    {
        return $this->chartDisplay;
    }

    /**
     * @return IChartCriteria|null
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @return bool
     */
    public function hasCriteria() : bool
    {
        return $this->criteria !== null;
    }

    /**
     * @return IChartDataTable
     */
    public function loadData() : IChartDataTable
    {
        return $this->chartDisplay->getDataSource()->load($this->criteria);
    }

    protected function hasExtraAuthorization() : bool
    {
        return true;
    }
}