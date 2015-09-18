<?php

namespace Iddigital\Cms\Core\Persistence\Db\Platform;

use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The database platform interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IPlatform
{
    /**
     * Maps the supplied value to the database format.
     *
     * @param Type  $type
     * @param mixed $value
     *
     * @return mixed
     */
    public function mapValueToDbFormat(Type $type, $value);

    /**
     * Maps the supplied row set to an array of arrays
     * with the values in a suitable database format.
     *
     * @param RowSet $rows
     *
     * @return array
     */
    public function mapResultSetToDbFormat(RowSet $rows);

    /**
     * Maps the supplied result set array to a row set object
     * containing the values in the equivalent PHP format.
     *
     * @param Table   $table
     * @param array[] $rows
     *
     * @return RowSet
     */
    public function mapResultSetToPhpForm(Table $table, array $rows);

    /**
     * Compiles the select query.
     *
     * @param Select $query
     *
     * @return CompiledQuery
     */
    public function compileSelect(Select $query);

    /**
     * Compiles the update query.
     *
     * @param Update $query
     *
     * @return CompiledQuery
     */
    public function compileUpdate(Update $query);

    /**
     * Compiles the delete query.
     *
     * @param Delete $query
     *
     * @return CompiledQuery
     */
    public function compileDelete(Delete $query);

    /**
     * Compiles a prepared insert query with the values as named parameters named with their respective column.
     *
     * @param Table $table
     *
     * @return string
     */
    public function compilePreparedInsert(Table $table);

    /**
     * Compiles a prepared update query with the values as named parameters with their respective column.
     * The columns in $whereColumns are *not* in the SET clause and instead a condition in the WHERE clause.
     *
     * @param Table    $table
     * @param string[] $whereColumns
     *
     * @return string
     */
    public function compilePreparedUpdate(Table $table, array $whereColumns);
}