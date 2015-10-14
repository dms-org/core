<?php

namespace Iddigital\Cms\Core\Module\Definition\Chart;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The chart definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartDefiner
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ITableDataSource[]
     */
    protected $tables;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * ChartDefiner constructor.
     *
     * @param string             $name
     * @param ITableDataSource[] $tables
     * @param callable           $callback
     */
    public function __construct($name, array $tables, callable $callback)
    {
        $this->name     = $name;
        $this->tables   = $tables;
        $this->callback = $callback;
    }

    /**
     * Defines the table of which to load the chart data from.
     *
     * @param string $tableName
     *
     * @return TableChartMappingDefiner
     * @throws InvalidArgumentException
     */
    public function fromTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            throw InvalidArgumentException::format(
                    'Invalid table name supplied to %s: expecting one of (%s), \'%s\' given',
                    __METHOD__, Debug::formatValues(array_keys($this->tables)), $tableName
            );
        }

        return new TableChartMappingDefiner($this->name, $this->tables[$tableName], $this->callback);
    }
}