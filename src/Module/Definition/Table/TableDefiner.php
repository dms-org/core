<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Util\Debug;

/**
 * The table definer class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableDefiner extends TableDefinerBase
{
    /**
     * @var ITableDisplay[]
     */
    protected $previousTables;

    /**
     * @inheritDoc
     */
    public function __construct($name, callable $callback, array $previousTables)
    {
        parent::__construct($name, $callback);
        $this->previousTables = $previousTables;
    }


    /**
     * Sets the data source as an array of rows.
     *
     * @param array[] $rows
     *
     * @return ArrayTableDefiner
     */
    public function fromArray(array $rows) : ArrayTableDefiner
    {
        return new ArrayTableDefiner($this->name, $this->callback, $rows);
    }

    /**
     * Sets the data source as a collection of objects.
     *
     * @param IObjectSet $objects
     *
     * @return ObjectTableDefiner
     */
    public function fromObjects(IObjectSet $objects) : ObjectTableDefiner
    {
        return new ObjectTableDefiner($this->name, $this->callback, $objects);
    }

    /**
     * Sets the data source as a previous table.
     *
     * @param string $tableName
     *
     * @return GroupedTableDefiner
     * @throws InvalidArgumentException
     */
    public function fromPreviousTable(string $tableName) : GroupedTableDefiner
    {
        if (!isset($this->previousTables[$tableName])) {
            throw InvalidArgumentException::format(
                'Invalid table name supplied to %s: expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->previousTables)), $tableName
            );
        }

        return new GroupedTableDefiner($this->name, $this->callback, $this->previousTables[$tableName]->getDataSource());
    }
}