<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine;

use Doctrine\DBAL\Query\QueryBuilder;
use Dms\Core\Persistence\Db\Platform\CompiledQuery;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;

/**
 * The SQL column resequence compiler interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IResequenceCompiler
{
    /**
     * Gets the sql statement to update the supplied column to a set of (1-based) incrementing integers
     * ordered against the current values in the column.
     *
     * @param QueryBuilder               $queryBuilder
     * @param ResequenceOrderIndexColumn $query
     *
     * @return CompiledQuery
     */
    public function compileResequenceQuery(QueryBuilder $queryBuilder, ResequenceOrderIndexColumn $query) : \Dms\Core\Persistence\Db\Platform\CompiledQuery;
}