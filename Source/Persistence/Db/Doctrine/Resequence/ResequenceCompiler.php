<?php

namespace Iddigital\Cms\Core\Persistence\Db\Doctrine\Resequence;

use Doctrine\DBAL\Query\QueryBuilder;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\DoctrineExpressionCompiler;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\IResequenceCompiler;
use Iddigital\Cms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;

/**
 * The resequence compiler base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ResequenceCompiler implements IResequenceCompiler
{
    /**
     * @var DoctrineExpressionCompiler
     */
    protected $expressionCompiler;

    /**
     * ResequenceCompiler constructor.
     *
     * @param DoctrineExpressionCompiler $expressionCompiler
     */
    public function __construct(DoctrineExpressionCompiler $expressionCompiler)
    {
        $this->expressionCompiler = $expressionCompiler;
    }

    /**
     * @param QueryBuilder               $queryBuilder
     * @param ResequenceOrderIndexColumn $query
     *
     * @return string
     */
    public function compileResequenceQuery(QueryBuilder $queryBuilder, ResequenceOrderIndexColumn $query)
    {
        $platform         = $queryBuilder->getConnection()->getDatabasePlatform();
        $outerTable       = $platform->quoteSingleIdentifier($query->getTable()->getName());
        $subSelectTable   = $platform->quoteSingleIdentifier($query->getTable()->getName() . '__inner');
        $orderIndexColumn = $platform->quoteSingleIdentifier($query->getColumn()->getName());

        $subSelect = $queryBuilder->getConnection()->createQueryBuilder();
        $subSelect
                ->from($outerTable, $outerTable . '__inner')
                ->select('COUNT(*) AS row_number')
                ->where($queryBuilder->expr()->lte(
                        $subSelectTable . '.' . $orderIndexColumn,
                        $outerTable . '.' . $orderIndexColumn
                ));

        $queryBuilder
                ->update($outerTable)
                ->set($orderIndexColumn, '(' . $subSelect->getSQL() . ')');


    }
}