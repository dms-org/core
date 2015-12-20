<?php

namespace Dms\Core\Widget;

use Dms\Core\Table\IDataTable;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableDataSource;

/**
 * The table widget class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableWidget extends Widget
{
    /**
     * @var ITableDataSource
     */
    protected $tableDataSource;

    /**
     * @var IRowCriteria|null
     */
    protected $criteria;

    /**
     * @inheritDoc
     */
    public function __construct($name, $label, ITableDataSource $tableDataSource, IRowCriteria $criteria = null)
    {
        parent::__construct($name, $label);
        $this->tableDataSource = $tableDataSource;
        $this->criteria        = $criteria;
    }

    /**
     * @return ITableDataSource
     */
    public function getTableDataSource()
    {
        return $this->tableDataSource;
    }

    /**
     * @return IRowCriteria|null
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @return bool
     */
    public function hasCriteria()
    {
        return $this->criteria !== null;
    }

    /**
     * @return IDataTable
     */
    public function loadData()
    {
        return $this->tableDataSource->load($this->criteria);
    }
}