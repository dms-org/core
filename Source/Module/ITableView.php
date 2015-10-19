<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Table\IRowCriteria;

/**
 * The table view interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITableView
{
    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Gets whether this is the default table view.
     *
     * @return bool
     */
    public function isDefault();

    /**
     * Gets whether the view contains criteria.
     *
     * @return bool
     */
    public function hasCriteria();

    /**
     * Gets the row criteria.
     *
     * @return IRowCriteria|null
     */
    public function getCriteria();
}