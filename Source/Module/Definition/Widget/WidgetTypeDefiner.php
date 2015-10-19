<?php

namespace Iddigital\Cms\Core\Module\Definition\Widget;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The widget type definer class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WidgetTypeDefiner extends WidgetDefinerBase
{
    /**
     * @var string
     */
    private $label;

    /**
     * @param string             $name
     * @param string             $label
     * @param ITableDataSource[] $tables
     * @param IChartDataSource[] $charts
     * @param callable           $callback
     */
    public function __construct($name, $label, array $tables, array $charts, callable $callback)
    {
        parent::__construct($name, $tables, $charts, $callback);
        $this->label = $label;
    }

    /**
     * Defines the table of which to load the widget from.
     *
     * @param string $tableName
     *
     * @return TableWidgetDefiner
     * @throws InvalidArgumentException
     */
    public function withTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            throw InvalidArgumentException::format(
                    'Invalid table name supplied to %s: expecting one of (%s), \'%s\' given',
                    __METHOD__, Debug::formatValues(array_keys($this->tables)), $tableName
            );
        }

        return new TableWidgetDefiner($this->name, $this->label, $this->tables[$tableName]->getDataSource(), $this->callback);
    }

    /**
     * Defines the chart of which to load the widget from.
     *
     * @param string $chartName
     *
     * @return ChartWidgetDefiner
     * @throws InvalidArgumentException
     */
    public function withChart($chartName)
    {
        if (!isset($this->charts[$chartName])) {
            throw InvalidArgumentException::format(
                    'Invalid table name supplied to %s: expecting one of (%s), \'%s\' given',
                    __METHOD__, Debug::formatValues(array_keys($this->charts)), $chartName
            );
        }

        return new ChartWidgetDefiner($this->name, $this->label, $this->charts[$chartName]->getDataSource(), $this->callback);
    }
}