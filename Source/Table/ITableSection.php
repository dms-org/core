<?php

namespace Dms\Core\Table;

use Dms\Core\Module\IParameterizedAction;

/**
 * The data table section interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITableSection
{
    /**
     * @return ITableStructure
     */
    public function getStructure();

    /**
     * @return bool
     */
    public function hasGroupData();

    /**
     * @return ITableRow|null
     */
    public function getGroupData();

    /**
     * @return ITableRow[]
     */
    public function getRows();
}