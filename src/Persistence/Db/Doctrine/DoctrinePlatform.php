<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Doctrine\Resequence\ResequenceCompilerFactory;
use Dms\Core\Persistence\Db\Platform\CompiledQuery;
use Dms\Core\Persistence\Db\Platform\CompiledQueryBuilder;
use Dms\Core\Persistence\Db\Platform\Platform;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Query;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Schema\Table;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Platforms\AbstractPlatform as DoctrineAbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;

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
    public function getDoctrinePlatform() : DoctrineAbstractPlatform
    {
        return $this->doctrinePlatform;
    }

    /**
     * @return DoctrineExpressionCompiler
     */
    public function getExpressionCompiler() : DoctrineExpressionCompiler
    {
        return $this->expressionCompiler;
    }

    /**
     * @inheritDoc
     */
    public function compilePreparedInsert(Table $table) : string
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
    public function compilePreparedUpdate(Table $table, array $updateColumns, array $whereColumnNameParameterMap) : string
    {
        $queryBuilder = $this->doctrineConnection->createQueryBuilder();

        $queryBuilder->update($this->identifier($table->getName()));

        foreach ($updateColumns as $columnName) {
            $queryBuilder->set($this->identifier($columnName), ':' . $columnName);
        }

        $wherePredicates = [];
        foreach ($whereColumnNameParameterMap as $columnName => $parameterName) {
            $wherePredicates[] = $this->identifier($columnName) . ' = :' . $parameterName;
        }

        if ($wherePredicates) {
            $queryBuilder->where(...$wherePredicates);
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
    protected function dateFormatString() : string
    {
        return $this->doctrinePlatform->getDateFormatString();
    }

    /**
     * @inheritDoc
     */
    protected function dateTimeFormatString() : string
    {
        return $this->doctrinePlatform->getDateTimeFormatString();
    }

    /**
     * @inheritDoc
     */
    protected function timeFormatString() : string
    {
        return $this->doctrinePlatform->getTimeFormatString();
    }

    /**
     * @inheritDoc
     */
    public function quoteIdentifier(string $value) : string
    {
        return $this->doctrinePlatform->quoteSingleIdentifier($value);
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

        $queryBuilder->from(
            $this->identifier($query->getTableName()),
            $query->isTableAliased() ? $this->identifier($query->getTableAlias()) : null
        );

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

        $queryBuilder->update(
            $this->identifier($query->getTableName()),
            $query->isTableAliased() ? $this->identifier($query->getTableAlias()) : null
        );

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

            $this->mergeQueryParameters($queryBuilder, $subQuery->getParameters());
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

        $queryBuilder->delete(
            $this->identifier($query->getTableName()),
            $query->isTableAliased() ? $this->identifier($query->getTableAlias()) : null
        );

        if ($query->getJoins() || $query->getOrderings() || $query->hasLimitOrOffset()) {
            $subQuery = $this->compileAsSubSelectPrimaryKey($query);
            $queryBuilder->where($queryBuilder->expr()->in(
                $this->identifier($query->getTable()->getPrimaryKeyColumnName()),
                $this->wrapInDerivedTable($subQuery, $query)
            ));

            $this->mergeQueryParameters($queryBuilder, $subQuery->getParameters());
        } else {
            $this->compileWhere($queryBuilder, $query);
        }

        $compiled->sql        = $queryBuilder->getSQL();
        $compiled->parameters = $queryBuilder->getParameters();
    }

    protected function mergeQueryParameters(QueryBuilder $queryBuilder, array $newParameters)
    {
        $currentPosition = max(array_filter(array_keys($queryBuilder->getParameters()), 'is_int') ?: [0]) + 1;

        foreach ($newParameters as $key => $value) {
            if (is_int($key)) {
                $queryBuilder->setParameter($currentPosition++, $value);
            } else {
                $queryBuilder->setParameter($key, $value);
            }
        }
    }

    private function compileAsSubSelectPrimaryKey(Query $query)
    {
        $fromTable = $query->getTable();

        $subSelect = Select::copyFrom($query);

        if ($fromTable->hasPrimaryKeyColumn()) {
            $subSelect->setColumns([
                $fromTable->getPrimaryKeyColumnName() => Expr::column($subSelect->getTableAlias(), $fromTable->getPrimaryKeyColumn())
            ]);
        } else {
            throw InvalidArgumentException::format(
                'Cannot select primary key from table \'%s\' for update/delete query: table has no primary key defined',
                $fromTable->getName()
            );
        }

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
            $queryBuilder->addGroupBy($this->compileExpression($queryBuilder, $grouping));
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
            $queryBuilder->addOrderBy(
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
                $this->identifier($join->getAlias()),
                $join->getOn() ? $this->compileExpression($queryBuilder, Expr::compoundAnd($join->getOn())) : '1=1'
            );

            $fromAlias = $join->getAlias();
        }
    }

    /**
     * @inheritDoc
     */
    public function compileResequenceOrderIndexColumn(ResequenceOrderIndexColumn $query) : \Dms\Core\Persistence\Db\Platform\CompiledQuery
    {
        return $this->resequenceCompiler->compileResequenceQuery($this->doctrineConnection->createQueryBuilder(), $query);
    }

    /**
     * @inheritDoc
     */
    protected function compileResequenceOrderIndexColumnQuery(ResequenceOrderIndexColumn $query, CompiledQueryBuilder $compiled)
    {

    }

    private function compileExpression(QueryBuilder $queryBuilder, Expr $expr, bool $subselectAlias = false)
    {
        return $this->expressionCompiler->compileExpression($queryBuilder, $expr, $subselectAlias);
    }

    /**
     * @param $identifier
     *
     * @return string
     */
    protected function identifier($identifier) : string
    {
        return $this->doctrinePlatform->quoteSingleIdentifier($identifier);
    }
}