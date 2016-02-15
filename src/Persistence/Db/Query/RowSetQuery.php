<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query;

use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Table;

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
    public function getTable() : \Dms\Core\Persistence\Db\Schema\Table
    {
        return $this->rows->getTable();
    }

    /**
     * @return RowSet
     */
    public function getRows() : \Dms\Core\Persistence\Db\RowSet
    {
        return $this->rows;
    }
}