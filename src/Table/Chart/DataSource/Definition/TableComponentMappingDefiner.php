<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart\DataSource\Definition;

use Dms\Core\Table\Chart\IChartAxis;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Column\Component\ColumnComponent;
use Dms\Core\Table\IColumnComponent;

/**
 * The table component to chart component mapping definer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableComponentMappingDefiner
{
    /**
     * @var IColumnComponent
     */
    private $component;

    /**
     * @var callable
     */
    private $componentCallback;

    /**
     * TableComponentMappingDefiner constructor.
     *
     * @param IColumnComponent $component
     * @param callable         $componentCallback
     */
    public function __construct(IColumnComponent $component, callable $componentCallback)
    {
        $this->component         = $component;
        $this->componentCallback = $componentCallback;
    }

    /**
     * Defines to map the table component to an axis with a single component.
     * The axis name/label default to the table component name/label respectively if null.
     *
     * @param string|null $axisName
     * @param string|null $axisLabel
     * @param string|null $componentName
     * @param string|null $componentLabel
     *
     * @return IChartAxis
     */
    public function toAxis(string $axisName = null, string $axisLabel = null, string $componentName = null, string $componentLabel = null) : \Dms\Core\Table\Chart\IChartAxis
    {
        $axisName  = $axisName ?: $this->component->getName();
        $axisLabel = $axisLabel ?: $this->component->getLabel();

        return new ChartAxis($axisName, $axisLabel, [
                $this->asComponent($componentName, $componentLabel)
        ]);
    }

    /**
     * Defines to map the table component to a chart axis component which is
     * return from the function.
     *
     * The axis component name/label default to the table component name/label respectively if null.
     *
     * @param string|null $componentName
     * @param string|null $componentLabel
     *
     * @return IColumnComponent
     */
    public function asComponent(string $componentName = null, string $componentLabel = null) : \Dms\Core\Table\IColumnComponent
    {
        $componentName  = $componentName ?: $this->component->getName();
        $componentLabel = $componentLabel ?: $this->component->getLabel();

        $chartAxisComponent = new ColumnComponent(
                $componentName,
                $componentLabel,
                $this->component->getType()
        );

        call_user_func($this->componentCallback, $chartAxisComponent);

        return $chartAxisComponent;
    }
}