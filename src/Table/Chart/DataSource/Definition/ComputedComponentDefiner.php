<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart\DataSource\Definition;

use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\IField;
use Dms\Core\Table\Chart\IChartAxis;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Column\Component\ColumnComponent;
use Dms\Core\Table\Column\Component\Type\ColumnComponentType;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\IColumnComponentType;

/**
 * The computed chart component definer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ComputedComponentDefiner
{
    /**
     * @var callable
     */
    private $componentCallback;

    /**
     * @var string[]
     */
    private $columnsToLoad = [];

    /**
     * ComputedComponentDefiner constructor.
     *
     * @param callable $componentCallback
     */
    public function __construct(callable $componentCallback)
    {
        $this->componentCallback = $componentCallback;
    }

    /**
     * Defines that the computed column requires the supplied columns
     * to be loaded from the underlying table.
     *
     * @param string[] $columnNames
     *
     * @return static
     */
    public function requiresColumns(array $columnNames)
    {
        $this->columnsToLoad = array_merge($this->columnsToLoad, $columnNames);

        return $this;
    }

    /**
     * Defines that the computed column requires the supplied columns
     * to be loaded from the underlying table.
     *
     * @param string $columnName
     *
     * @return static
     */
    public function requiresColumn(string $columnName)
    {
        $this->columnsToLoad[] = $columnName;

        return $this;
    }

    /**
     * Defines to map the table component to an axis with a single component.
     * The axis name/label default to the table component name/label respectively if null.
     *
     * @param string                                       $axisName
     * @param string                                       $axisLabel
     * @param IColumnComponentType|IField|FieldBuilderBase $componentType
     * @param string|null                                  $componentName
     * @param string|null                                  $componentLabel
     *
     * @return IChartAxis
     */
    public function toAxis(string $axisName, string $axisLabel, $componentType, string $componentName = null, string $componentLabel = null) : \Dms\Core\Table\Chart\IChartAxis
    {
        $componentName  = $componentName ?: $axisName;
        $componentLabel = $componentLabel ?: $axisLabel;

        return new ChartAxis($axisName, $axisLabel, [
                $this->asComponent($componentName, $componentLabel, $componentType)
        ]);
    }

    /**
     * Defines to map the table component to a chart axis component which is
     * return from the function.
     *
     * The axis component name/label default to the table component name/label respectively if null.
     *
     * @param string                                       $componentName
     * @param string                                       $componentLabel
     * @param IColumnComponentType|IField|FieldBuilderBase $componentType
     *
     * @return IColumnComponent
     */
    public function asComponent(string $componentName, string $componentLabel, $componentType) : \Dms\Core\Table\IColumnComponent
    {
        if ($componentType instanceof FieldBuilderBase) {
            $componentType = ColumnComponentType::forField($componentType->build()->withName($componentName, $componentLabel));
        } elseif ($componentType instanceof IField) {
            $componentType = ColumnComponentType::forField($componentType);
        }

        $chartAxisComponent = new ColumnComponent(
                $componentName,
                $componentLabel,
                $componentType
        );

        call_user_func($this->componentCallback, $chartAxisComponent, $this->columnsToLoad);

        return $chartAxisComponent;
    }
}