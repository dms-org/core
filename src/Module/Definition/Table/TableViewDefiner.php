<?php declare(strict_types = 1);

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
    public function __construct(ITableDataSource $dataSource, string $name, string $label)
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
    public function finalize() : \Dms\Core\Module\Table\TableView
    {
        return new TableView(
                $this->name,
                $this->label,
                $this->default,
                RowCriteria::fromExisting($this)
        );
    }
}