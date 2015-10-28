<?php

namespace Iddigital\Cms\Core\Persistence\Db\Platform;

use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
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
     * Note: row keys are maintained.
     *
     * @param RowSet      $rows
     * @param string|null $lockingColumnDataPrefix
     *
     * @return array
     */
    public function mapResultSetToDbFormat(RowSet $rows, $lockingColumnDataPrefix = null);

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
     * Compiles an array of queries that will update the supplied column to a set of (1-based) incrementing integers
     * ordered against the current values in the column.
     *
     * This can be used to remove duplicates and gaps within the existing values.
     *
     * @param ResequenceOrderIndexColumn $query
     *
     * @return CompiledQuery
     */
    public function compileResequenceOrderIndexColumn(ResequenceOrderIndexColumn $query);

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
     *
     * @param Table    $table
     * @param string[] $updateColumns
     * @param string[] $whereColumnNameParameterMap
     *
     * @return string
     */
    public function compilePreparedUpdate(Table $table, array $updateColumns, array $whereColumnNameParameterMap);
}