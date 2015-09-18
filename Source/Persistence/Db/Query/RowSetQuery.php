<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query;

use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The row set query base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RowSetQuery implements IQuery
{
    /**
     * @var RowSet
     */
    protected $rows;

    /**
     * @param RowSet $rows
     */
    public function __construct(RowSet $rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->rows->getTable();
    }

    /**
     * @return RowSet
     */
    public function getRows()
    {
        return $this->rows;
    }
}