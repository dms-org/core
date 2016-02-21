<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Module\ITableDisplay;
use Dms\Core\Table\IDataTable;
use Dms\Core\Table\IRowCriteria;

/**
 * The table widget class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableWidget extends Widget
{
    /**
     * @var ITableDisplay
     */
    protected $tableDisplay;

    /**
     * @var IRowCriteria|null
     */
    protected $criteria;

    /**
     * @inheritDoc
     */
    public function __construct($name, $label, ITableDisplay $tableDisplay, IRowCriteria $criteria = null)
    {
        parent::__construct($name, $label);
        $this->tableDisplay = $tableDisplay;
        $this->criteria     = $criteria;
    }

    /**
     * @return ITableDisplay
     */
    public function getTableDisplay() : ITableDisplay
    {
        return $this->tableDisplay;
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
    public function hasCriteria() : bool
    {
        return $this->criteria !== null;
    }

    /**
     * @return IDataTable
     */
    public function loadData() : IDataTable
    {
        return $this->tableDisplay->getDataSource()->load($this->criteria);
    }

    /**
     * Returns whether the current user authorized to see this widget.
     *
     * @return bool
     */
    public function isAuthorized() : bool
    {
        return true;
    }
}