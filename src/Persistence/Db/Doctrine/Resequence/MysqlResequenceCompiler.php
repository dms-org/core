<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine\Resequence;

use Doctrine\DBAL\Query\QueryBuilder;
use Dms\Core\Persistence\Db\Platform\CompiledQuery;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;

/**
 * The mysql implementation for the column resequence compiler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MysqlResequenceCompiler extends ResequenceCompiler
{
    /**
     * @param QueryBuilder               $queryBuilder
     * @param ResequenceOrderIndexColumn $query
     *
     * @return CompiledQuery
     */
    public function compileResequenceQuery(QueryBuilder $queryBuilder, ResequenceOrderIndexColumn $query) : CompiledQuery
    {
        $platform   = $queryBuilder->getConnection()->getDatabasePlatform();
        $primaryKey = $platform->quoteSingleIdentifier($query->getTable()->getPrimaryKeyColumnName());
        $table      = $platform->quoteSingleIdentifier($query->getTable()->getName());
        $column     = $platform->quoteSingleIdentifier($query->getColumn()->getName());
        $where      = $query->hasWhereCondition()
                ? $this->expressionCompiler->compileExpression($queryBuilder, $query->getWhereCondition())
                : '1=1';

        if ($query->hasGroupingColumn()) {
            $groupingColumnName = $platform->quoteSingleIdentifier($query->getGroupingColumn()->getName());

            $sql          = <<<SQL
UPDATE {$table}
SET 
    {$column} = (@__rownum:=IF({$groupingColumnName} = @__previous_group, @__rownum, 0) + 1),
	{$primaryKey} = IF((@__previous_group:={$groupingColumnName}) IS NOT NULL, {$primaryKey}, {$primaryKey})
WHERE {$where}
ORDER BY {$groupingColumnName} , {$column}, @__previous_group := NULL, @__rownum := 0
SQL;
        } else {
            $sql = <<<SQL
UPDATE {$table}
SET 
    {$column} = (@__rownum:= @__rownum + 1)
WHERE {$where}
ORDER BY {$column}, @__rownum := 0
SQL;
        }

        return new CompiledQuery($sql, $queryBuilder->getParameters());
    }
}