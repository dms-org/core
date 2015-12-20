<?php

namespace Dms\Core\Persistence\Db\Doctrine\Resequence;

use Doctrine\DBAL\Query\QueryBuilder;
use Dms\Core\Persistence\Db\Platform\CompiledQuery;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;

/**
 * The default implementation for the column resequence compiler.
 *
 * This is rather inefficient using subselects to determine row number
 * and hence should only be used if no better option is available.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultResequenceCompiler extends ResequenceCompiler
{
    /**
     * @param QueryBuilder               $queryBuilder
     * @param ResequenceOrderIndexColumn $query
     *
     * @return CompiledQuery
     */
    public function compileResequenceQuery(QueryBuilder $queryBuilder, ResequenceOrderIndexColumn $query)
    {
        $platform         = $queryBuilder->getConnection()->getDatabasePlatform();
        $tableName        = $platform->quoteSingleIdentifier($query->getTable()->getName());
        $subSelectTable   = $platform->quoteSingleIdentifier($query->getTable()->getName() . '__inner');
        $orderIndexColumn = $platform->quoteSingleIdentifier($query->getColumn()->getName());

        $subSelect = $queryBuilder->getConnection()->createQueryBuilder();
        $subSelect
                ->from($tableName, $subSelectTable)
                ->select('COUNT(*) AS row_number')
                ->where($queryBuilder->expr()->lte(
                        $subSelectTable . '.' . $orderIndexColumn,
                        $tableName . '.' . $orderIndexColumn
                ));

        if ($query->hasGroupingColumn()) {
            $groupingColumn = $platform->quoteSingleIdentifier($query->getGroupingColumn()->getName());

            $subSelect->andWhere($queryBuilder->expr()->eq(
                    $subSelectTable . '.' . $groupingColumn,
                    $tableName . '.' . $groupingColumn
            ));
        }

        $queryBuilder
                ->update($tableName)
                ->set($orderIndexColumn, '(' . $subSelect->getSQL() . ')');

        if ($query->hasWhereCondition()) {
            $queryBuilder->where(
                    $this->expressionCompiler->compileExpression($queryBuilder, $query->getWhereCondition())
            );
        }

        return new CompiledQuery($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }
}