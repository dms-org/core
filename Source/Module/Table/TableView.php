<?php

namespace Iddigital\Cms\Core\Module\Table;

use Iddigital\Cms\Core\Module\ITableView;
use Iddigital\Cms\Core\Table\Criteria\RowCriteria;
use Iddigital\Cms\Core\Table\IRowCriteria;

/**
 * The table view class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableView implements ITableView
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $default;

    /**
     * @var IRowCriteria|null
     */
    protected $criteria;

    /**
     * TableView constructor.
     *
     * @param string            $name
     * @param string            $label
     * @param bool              $default
     * @param IRowCriteria|null $criteria
     */
    public function __construct($name, $label, $default, IRowCriteria $criteria = null)
    {
        $this->name     = $name;
        $this->label    = $label;
        $this->default  = $default;
        $this->criteria = $criteria;
    }

    /**
     * @return TableView
     */
    public static function createDefault()
    {
        return new self('default', 'Default', true);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function hasCriteria()
    {
        return $this->criteria !== null;
    }

    /**
     * @return IRowCriteria|null
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @inheritDoc
     */
    public function getCriteriaCopy()
    {
        return $this->criteria ? $this->criteria->asNewCriteria() : null;
    }
}