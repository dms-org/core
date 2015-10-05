<?php

namespace Iddigital\Cms\Core\Module\Definition\Table;

use Iddigital\Cms\Core\Table\Builder\Table;
use Iddigital\Cms\Core\Table\DataSource\ArrayTableDataSource;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\ITableStructure;

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
     * @return void
     */
    public function withStructure(ITableStructure $structure)
    {
        call_user_func($this->callback, new ArrayTableDataSource($this->name, $structure, $this->data));
    }

    /**
     * Defines the structure of the table with the supplied columns.
     *
     * @param IColumn[] $columns
     *
     * @return void
     */
    public function withColumns(array $columns)
    {
        $this->withStructure(Table::create($columns));
    }
}