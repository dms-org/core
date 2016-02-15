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
    public function compileResequenceQuery(QueryBuilder $queryBuilder, ResequenceOrderIndexColumn $query) : \Dms\Core\Persistence\Db\Platform\CompiledQuery
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

            $subSelect          = <<<SQL
SELECT
  {$primaryKey},
  (@__rownum := IF({$groupingColumnName} = @__previous_group, @__rownum, 0) + 1) AS row_number,
  (@__previous_group := {$groupingColumnName})
FROM
 {$table},
 (SELECT @__previous_group := NULL) AS __previous_group,
 (SELECT @__rownum := 0) AS row_num
ORDER BY {$groupingColumnName}, {$column}
SQL;
        } else {
            $subSelect = <<<SQL
SELECT
  {$primaryKey},
  (@__rownum := @__rownum + 1) AS row_number
FROM
 {$table},
 (SELECT @__rownum := 0) AS row_num
ORDER BY {$column}
SQL;
        }
        $sql = <<<SQL
UPDATE {$table}
INNER JOIN ($subSelect) AS __row_numbers USING ({$primaryKey})
SET
{$column} = __row_numbers.row_number
WHERE {$where}
SQL;

        return new CompiledQuery($sql, $queryBuilder->getParameters());
    }
}