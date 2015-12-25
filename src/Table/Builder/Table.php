<?php

namespace Dms\Core\Table\Builder;

use Dms\Core\Table\IColumn;
use Dms\Core\Table\ITableStructure;
use Dms\Core\Table\TableStructure;

/**
 * The table structure builder class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Table
{
    /**
     * @param IColumn[] $columns
     *
     * @return ITableStructure
     */
    public static function create(array $columns)
    {
        return new TableStructure($columns);
    }
}