<?php

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Module\Table\TableView;
use Dms\Core\Table\Criteria\RowCriteria;
use Dms\Core\Table\ITableDataSource;

/**
 * The table view definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableViewDefiner extends RowCriteria
{
    /**
     * @var ITableDataSource
     */
    private $dataSource;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $default = false;

    /**
     * TableViewDefiner constructor.
     *
     * @param ITableDataSource $dataSource
     * @param string           $name
     * @param string           $label
     */
    public function __construct(ITableDataSource $dataSource, $name, $label)
    {
        parent::__construct($dataSource->getStructure());

        $this->dataSource = $dataSource;
        $this->name       = $name;
        $this->label      = $label;
    }

    /**
     * Defines this view to be the default view.
     *
     * @return static
     */
    public function asDefault()
    {
        $this->default = true;

        return $this;
    }

    /**
     * @return TableView
     */
    public function finalize()
    {
        return new TableView(
                $this->name,
                $this->label,
                $this->default,
                RowCriteria::fromExisting($this)
        );
    }
}