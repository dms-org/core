<?php

namespace Iddigital\Cms\Core\Persistence\Db\Doctrine;

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Platforms\AbstractPlatform as DoctrineAbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\Resequence\ResequenceCompilerFactory;
use Iddigital\Cms\Core\Persistence\Db\Platform\CompiledQuery;
use Iddigital\Cms\Core\Persistence\Db\Platform\CompiledQueryBuilder;
use Iddigital\Cms\Core\Persistence\Db\Platform\Platform;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Query;
use Iddigital\Cms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The doctrine platform.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrinePlatform extends Platform
{
    /**
     * @var DbalConnection
     */
    protected $doctrineConnection;

    /**
     * @var DoctrineAbstractPlatform
     */
    protected $doctrinePlatform;

    /**
     * @var DoctrineExpressionCompiler
     */
    protected $expressionCompiler;

    /**
     * @var IResequenceCompiler
     */
    protected $resequenceCompiler;

    /**
     * DoctrinePlatform constructor.
     *
     * @param DbalConnection $doctrineConnection
     */
    public function __construct(DbalConnection $doctrineConnection)
    {
        $this->doctrineConnection = $doctrineConnection;
        $this->doctrinePlatform   = $doctrineConnection->getDatabasePlatform();
        $this->expressionCompiler = new DoctrineExpressionCompiler($this);
        $this->resequenceCompiler = ResequenceCompilerFactory::buildFor($this);

        parent::__construct();
    }

    /**
     * @return DoctrineAbstractPlatform
     */
    public function getDoctrinePlatform()
    {
        return $this->doctrinePlatform;
    }

    /**
     * @return DoctrineExpressionCompiler
     */
    public function getExpressionCompiler()
    {
        return $this->expressionCompiler;
    }

    /**
     * @inheritDoc
     */
    public function compilePreparedInsert(Table $table)
    {
        $queryBuilder = $this->doctrineConnection->createQueryBuilder();

        $values                  = $this->createColumnParameterArray($queryBuilder, $table);
        $escapedIdentifierValues = [];

        foreach ($values as $column => $parameter) {
            $escapedIdentifierValues[$this->identifier($column)] = $parameter;
        }

        return $queryBuilder
                ->insert($this->identifier($table->getName()))
                ->values($escapedIdentifierValues)
                ->getSQL();
    }

    /**
     * @inheritDoc
     */
    public function compilePreparedUpdate(Table $table, array $updateColumns, array $whereColumnNameParameterMap)
    {
        $queryBuilder = $this->doctrineConnection->createQueryBuilder();

        $queryBuilder->update($this->identifier($table->getName()));

        foreach ($updateColumns as $columnName) {
            $queryBuilder->set($this->identifier($columnName), ':' . $columnName);
        }

        foreach ($whereColumnNameParameterMap as $columnName => $parameterName) {
            $queryBuilder->where($this->identifier($columnName) . ' = :' . $parameterName);
        }

        return $queryBuilder->getSQL();
    }

    protected function createColumnParameterArray(QueryBuilder $queryBuilder, Table $table)
    {
        $parameters = [];

        foreach ($table->getColumns() as $name => $column) {
            $parameters[$name] = ':' . $name;
        }

        return $parameters;
    }

    /**
     * @inheritDoc
     */
    protected function dateFormatString()
    {
        return $this->doctrinePlatform->getDateFormatString();
    }

    /**
     * @inheritDoc
     */
    protected function dateTimeFormatString()
    {
        return $this->doctrinePlatform->getDateTimeFormatString();
    }

    /**
     * @inheritDoc
     */
    protected function timeFormatString()
    {
        return $this->doctrinePlatform->getTimeFormatString();
    }

    /**
     * @inheritDoc
     */
    protected function compileSelectQuery(Select $query, CompiledQueryBuilder $compiled)
    {
        $queryBuilder = $this->doctrineConnection->createQueryBuilder();

        foreach ($query->getAliasColumnMap() as $alias => $expression) {
            $queryBuilder->addSelect(
                    $this->compileExpression($queryBuilder, $expression) . ' AS ' . $this->identifier($alias)
            );
        }

        $queryBuilder->from($this->identifier($query->getTableName()),
                $query->isTableAliased() ? $this->identifier($query->getTableAlias()) : null);

        $this->compileJoins($queryBuilder, $query);
        $this->compileWhere($queryBuilder, $query);
        $this->compileGroupBy($queryBuilder, $query);
        $this->compileHaving($queryBuilder, $query);
        $this->compileOrderBy($queryBuilder, $query);
        $this->compileLimitAndOffset($queryBuilder, $query);

        $compiled->sql        = $queryBuilder->getSQL();
        $compiled->parameters = $queryBuilder->getParameters();
    }

    /**
     * @inheritDoc
     */
    protected function compileUpdateQuery(Update $query, CompiledQueryBuilder $compiled)
    {
        $queryBuilder = $this->doctrineConnection->createQueryBuilder();

        $queryBuilder->update($this->identifier($query->getTableName()),
                $query->isTableAliased() ? $this->identifier($query->getTableAlias()) : null);

        foreach ($query->getColumnSetMap() as $columnName => $expression) {
            $queryBuilder->set(
                    $this->identifier($columnName),
                    $this->compileExpression($queryBuilder, $expression)
            );
        }

        if ($query->getJoins() || $query->getOrderings() || $query->hasLimitOrOffset()) {
            $subQuery = $this->compileAsSubSelectPrimaryKey($query);
            $queryBuilder->where($queryBuilder->expr()->in(
                    $this->identifier($query->getTable()->getPrimaryKeyColumnName()),
                    $this->wrapInDerivedTable($subQuery, $query)
            ));
            $queryBuilder->setParameters($subQuery->getParameters());
        } else {
            $this->compileWhere($queryBuilder, $query);
        }

        $compiled->sql        = $queryBuilder->getSQL();
        $compiled->parameters = $queryBuilder->getParameters();
    }

    /**
     * @inheritDoc
     */
    protected function compileDeleteQuery(Delete $query, CompiledQueryBuilder $compiled)
    {
        $queryBuilder = $this->doctrineConnection->createQueryBuilder();

        $queryBuilder->delete($this->identifier($query->getTableName()),
                $query->isTableAliased() ? $this->identifier($query->getTableAlias()) : null);

        if ($query->getJoins() || $query->getOrderings() || $query->hasLimitOrOffset()) {
            $subQuery = $this->compileAsSubSelectPrimaryKey($query);
            $queryBuilder->where($queryBuilder->expr()->in(
                    $this->identifier($query->getTable()->getPrimaryKeyColumnName()),
                    $this->wrapInDerivedTable($subQuery, $query)
            ));
            $queryBuilder->setParameters($subQuery->getParameters());
        } else {
            $this->compileWhere($queryBuilder, $query);
        }

        $compiled->sql        = $queryBuilder->getSQL();
        $compiled->parameters = $queryBuilder->getParameters();
    }

    private function compileAsSubSelectPrimaryKey(Query $query)
    {
        $fromTable = $query->getTable();
        $subSelect = Select::copyFrom($query);
        $subSelect->copyFrom($query);

        $subSelect->setColumns([
                $fromTable->getPrimaryKeyColumnName() => Expr::column($subSelect->getTableAlias(), $fromTable->getPrimaryKeyColumn())
        ]);

        return $this->compileSelect($subSelect);
    }

    private function wrapInDerivedTable(CompiledQuery $subQuery, Query $query)
    {
        $queryBuilder = $this->doctrineConnection->createQueryBuilder();

        $queryBuilder
                ->select('*')
                ->from('(' . $subQuery->getSql() . ')', $this->identifier($query->getTableAlias()));

        return $queryBuilder->getSQL();
    }

    protected function compileWhere(QueryBuilder $queryBuilder, Query $query)
    {
        if ($query->getWhere()) {
            $queryBuilder->where($this->compileExpression($queryBuilder, Expr::compoundAnd($query->getWhere())));
        }
    }

    protected function compileGroupBy(QueryBuilder $queryBuilder, Select $query)
    {
        foreach ($query->getGroupBy() as $grouping) {
            $queryBuilder->groupBy($this->compileExpression($queryBuilder, $grouping));
        }
    }

    protected function compileHaving(QueryBuilder $queryBuilder, Select $query)
    {
        if ($query->getHaving()) {
            $queryBuilder->having($this->compileExpression($queryBuilder, Expr::compoundAnd($query->getHaving())));
        }
    }

    protected function compileOrderBy(QueryBuilder $queryBuilder, Query $query)
    {
        foreach ($query->getOrderings() as $ordering) {
            $queryBuilder->orderBy(
                    $this->compileExpression($queryBuilder, $ordering->getExpression()),
                    $ordering->isAsc() ? 'ASC' : 'DESC'
            );
        }
    }

    protected function compileLimitAndOffset(QueryBuilder $queryBuilder, Query $query)
    {
        if ($query->hasLimitOrOffset()) {
            $queryBuilder
                    ->setFirstResult($query->getOffset())
                    ->setMaxResults($query->getLimit());
        }
    }

    private function compileJoins(QueryBuilder $queryBuilder, Query $query)
    {
        $fromAlias = $query->getTableAlias();

        foreach ($query->getJoins() as $join) {
            switch ($join->getType()) {
                case Join::INNER:
                    $method = 'innerJoin';
                    break;
                case Join::RIGHT:
                    $method = 'rightJoin';
                    break;
                case Join::LEFT:
                    $method = 'leftJoin';
                    break;
                default:
                    throw InvalidArgumentException::format('Unknown join type: %s', $join->getType());
            }

            $queryBuilder->{$method}(
                    $this->identifier($fromAlias),
                    $this->identifier($join->getTableName()),
                    $join->isTableAliased() ? $this->identifier($join->getAlias()) : null,
                    $join->getOn() ? $this->compileExpression($queryBuilder, Expr::compoundAnd($join->getOn())) : '1=1'
            );

            $fromAlias = $join->getAlias();
        }
    }

    /**
     * @inheritDoc
     */
    public function compileResequenceOrderIndexColumn(ResequenceOrderIndexColumn $query)
    {
        return $this->resequenceCompiler->compileResequenceQuery($this->doctrineConnection->createQueryBuilder(), $query);
    }

    /**
     * @inheritDoc
     */
    protected function compileResequenceOrderIndexColumnQuery(ResequenceOrderIndexColumn $query, CompiledQueryBuilder $compiled)
    {

    }

    private function compileExpression(QueryBuilder $queryBuilder, Expr $expr)
    {
        return $this->expressionCompiler->compileExpression($queryBuilder, $expr);
    }

    /**
     * @param $identifier
     *
     * @return string
     */
    protected function identifier($identifier)
    {
        return $this->doctrinePlatform->quoteSingleIdentifier($identifier);
    }
}