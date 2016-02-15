<?php declare(strict_types = 1);

namespace Dms\Core\Table;

/**
 * The data table interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IDataTable
{
    /**
     * @return ITableStructure
     */
    public function getStructure() : ITableStructure;

    /**
     * @return ITableSection[]
     */
    public function getSections() : array;
}