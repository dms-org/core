<?php

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Table\Builder\Table;
use Dms\Core\Table\DataSource\ArrayTableDataSource;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\ITableStructure;

/**
 * The array table definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayTableDefiner extends TableDefinerBase
{
    /**
     * @var array[]
     */
    private $data;

    /**
     * ArrayTableDefiner constructor.
     *
     * @param string   $name
     * @param callable $callback
     * @param array[]  $data
     */
    public function __construct($name, callable $callback, array $data)
    {
        parent::__construct($name, $callback);
        $this->data = $data;
    }

    /**
     * Defines the structure of the table.
     *
     * @param ITableStructure $structure
     *
     * @return TableViewsDefiner
     */
    public function withStructure(ITableStructure $structure)
    {
        return new TableViewsDefiner($this->name, $this->callback, new ArrayTableDataSource($structure, $this->data));
    }

    /**
     * Defines the structure of the table with the supplied columns.
     *
     * @param IColumn[] $columns
     *
     * @return TableViewsDefiner
     */
    public function withColumns(array $columns)
    {
        return $this->withStructure(Table::create($columns));
    }
}