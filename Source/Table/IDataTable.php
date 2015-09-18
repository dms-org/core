<?php

namespace Iddigital\Cms\Core\Table;

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
    public function getStructure();

    /**
     * @return ITableSection[]
     */
    public function getSections();
}