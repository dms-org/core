<?php declare(strict_types = 1);

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
    public function getStructure() : ITableStructure;

    /**
     * @return bool
     */
    public function hasGroupData() : bool;

    /**
     * @return ITableRow|null
     */
    public function getGroupData();

    /**
     * @return ITableRow[]
     */
    public function getRows() : array;

    /**
     * @return array[]
     */
    public function getRowArray() : array;
}