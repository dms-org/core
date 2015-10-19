<?php

namespace Iddigital\Cms\Core\Module\Definition\Table;

use Iddigital\Cms\Core\Module\ITableView;
use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * The table views definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableViewDefinition
{
    /**
     * @var ITableDataSource
     */
    private $dataSource;

    /**
     * @var TableViewDefiner[]
     */
    private $viewDefiners = [];

    /**
     * TableViewDefinition constructor.
     *
     * @param ITableDataSource $dataSource
     */
    public function __construct(ITableDataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * Defines a new view with the supplied name and label.
     *
     * @param string $name
     * @param string $label
     *
     * @return TableViewDefiner
     */
    public function name($name, $label)
    {
        $definer = new TableViewDefiner($this->dataSource, $name, $label);

        $this->viewDefiners[] = $definer;

        return $definer;
    }

    /**
     * @return ITableView[]
     */
    public function finalize()
    {
        $views = [];

        foreach ($this->viewDefiners as $definer) {
            $views[] = $definer->finalize();
        }

        return $views;
    }
}