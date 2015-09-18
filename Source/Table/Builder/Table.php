<?php

namespace Iddigital\Cms\Core\Table\Builder;

use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\ITableStructure;
use Iddigital\Cms\Core\Table\TableStructure;

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