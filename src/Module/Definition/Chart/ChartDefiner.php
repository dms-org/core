<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Chart;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Util\Debug;

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
     * @var ITableDisplay[]
     */
    protected $tables;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * ChartDefiner constructor.
     *
     * @param string          $name
     * @param ITableDisplay[] $tables
     * @param callable        $callback
     */
    public function __construct(string $name, array $tables, callable $callback)
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
    public function fromTable(string $tableName) : TableChartMappingDefiner
    {
        if (!isset($this->tables[$tableName])) {
            throw InvalidArgumentException::format(
                    'Invalid table name supplied to %s: expecting one of (%s), \'%s\' given',
                    __METHOD__, Debug::formatValues(array_keys($this->tables)), $tableName
            );
        }

        return new TableChartMappingDefiner($this->name, $this->tables[$tableName]->getDataSource(), $this->callback);
    }
}