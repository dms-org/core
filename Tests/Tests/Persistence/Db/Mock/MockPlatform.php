<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mock;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Persistence\Db\Platform\CompiledQueryBuilder;
use Iddigital\Cms\Core\Persistence\Db\Platform\Platform;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Ordering;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Aggregate;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\BinOp;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\ColumnExpr;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Count;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Max;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Parameter;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Tuple;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\UnaryOp;
use Iddigital\Cms\Core\Persistence\Db\Query\Query;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Pinq\Collection;
use Pinq\Direction;
use Pinq\ICollection;
use Pinq\Iterators\Common\Identity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockPlatform extends Platform
{
    /**
     * @return string
     */
    protected function dateFormatString()
    {
        return 'Y-m-d';
    }

    /**
     * @return string
     */
    protected function dateTimeFormatString()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * @return string
     */
    protected function timeFormatString()
    {
        return 'H:i:s';
    }

    public function compilePreparedInsert(Table $table)
    {
        return new PhpPreparedCompiledQuery(function (MockDatabase $database, array $parameters) use ($table) {
            $table = $database->getTable($table->getName());

            $table->insert($parameters);

            return 1;
        });
    }

    public function compilePreparedUpdate(Table $table, array $updateColumns, array $whereColumnNameParameterMap)
    {
        return new PhpPreparedCompiledQuery(function (MockDatabase $database, array $parameters) use (
                $table,
                $updateColumns,
                $whereColumnNameParameterMap
        ) {
            $affectedRows = 0;

            $primaryKey = $table->getPrimaryKeyColumnName();
            $table      = $database->getTable($table->getName());

            $whereConditions = [];
            foreach ($whereColumnNameParameterMap as $column => $parameterName) {
                $whereConditions[$column] = $parameters[$parameterName];
            }

            $updatedData = [];
            foreach ($updateColumns as $columnName) {
                $updatedData[$columnName] = $parameters[$columnName];
            }

            foreach ($table->getRows() as $row) {
                foreach ($whereConditions as $column => $value) {
                    if ($row[$column] !== $value && $value !== null) {
                        continue 2;
                    }
                }

                $table->update($row[$primaryKey], $updatedData + $row);
                $affectedRows++;
            }

            return $affectedRows;
        });
    }

    protected function compileExpressions(array $exprs)
    {
        $compiled = [];

        foreach ($exprs as $key => $expr) {
            $compiled[$key] = $this->compileExpression($expr);
        }

        return $compiled;
    }

    protected function compileExpression(Expr $expr)
    {
        switch (true) {
            case $expr instanceof BinOp:
                return $this->compileBinOp($expr);

            case $expr instanceof UnaryOp:
                return $this->compileUnaryOp($expr);

            case $expr instanceof ColumnExpr:
                $table  = $expr->getTable();
                $column = $expr->getName();

                return function ($row) use ($table, $column) {
                    if ($row instanceof ICollection) {
                        $row = $row->first();
                    }

                    return $row[$table][$column];
                };

            case $expr instanceof Count:
                return function (ICollection $group) {
                    return $group->count();
                };

            case $expr instanceof Max:
                $argument = $this->compileExpression($expr->getArgument());

                return function (ICollection $group) use ($argument) {
                    return $group->maximum($argument);
                };

            case $expr instanceof Parameter:
                $parameter = $this->mapValueToDbFormat($expr->getType(), $expr->getValue());

                return function ($row) use ($parameter) {
                    return $parameter;
                };

            case $expr instanceof Tuple:
                $compiled = $this->compileExpressions($expr->getExpressions());

                return function ($row) use ($compiled) {
                    $values = [];
                    foreach ($compiled as $inner) {
                        $values[] = $inner($row);
                    }

                    return $values;
                };

        }
    }

    private function compileBinOp(BinOp $expr)
    {
        $left  = $this->compileExpression($expr->getLeft());
        $right = $this->compileExpression($expr->getRight());

        switch ($expr->getOperator()) {
            case BinOp::AND_:
                return function ($row) use ($left, $right) {
                    return $left($row) && $right($row);
                };
            case BinOp::OR_:
                return function ($row) use ($left, $right) {
                    return $left($row) || $right($row);
                };
            case BinOp::EQUAL:
                return function ($row) use ($left, $right) {
                    return $left($row) == $right($row);
                };
            case BinOp::NOT_EQUAL:
                return function ($row) use ($left, $right) {
                    return $left($row) != $right($row);
                };
            case BinOp::LESS_THAN:
                return function ($row) use ($left, $right) {
                    return $left($row) < $right($row);
                };
            case BinOp::LESS_THAN_OR_EQUAL:
                return function ($row) use ($left, $right) {
                    return $left($row) <= $right($row);
                };
            case BinOp::GREATER_THAN:
                return function ($row) use ($left, $right) {
                    return $left($row) > $right($row);
                };
            case BinOp::GREATER_THAN_OR_EQUAL:
                return function ($row) use ($left, $right) {
                    return $left($row) >= $right($row);
                };
            case BinOp::STR_CONTAINS:
                return function ($row) use ($left, $right) {
                    return strpos($left($row), $right($row)) !== false;
                };
            case BinOp::STR_CONTAINS_CASE_INSENSITIVE:
                return function ($row) use ($left, $right) {
                    return stripos($left($row), $right($row)) !== false;
                };
            case BinOp::IN:
                return function ($row) use ($left, $right) {
                    return in_array($left($row), $right($row));
                };
            case BinOp::NOT_IN:
                return function ($row) use ($left, $right) {
                    return !in_array($left($row), $right($row));
                };
        }

        throw NotImplementedException::format('Unknown bin op %s', $expr->getOperator());
    }

    private function compileUnaryOp(UnaryOp $expr)
    {
        $operand = $this->compileExpression($expr->getOperand());

        switch ($expr->getOperator()) {
            case UnaryOp::IS_NULL:
                return function ($row) use ($operand) {
                    return $operand($row) === null;
                };

            case UnaryOp::IS_NOT_NULL:
                return function ($row) use ($operand) {
                    return $operand($row) !== null;
                };

            case UnaryOp::NOT:
                return function ($row) use ($operand) {
                    return !$operand($row);
                };
        }

        throw NotImplementedException::format('Unknown unary op %s', $expr->getOperator());
    }

    /**
     * @inheritDoc
     */
    public function compileSelect(Select $query)
    {
        $compiledQuery = function (MockDatabase $database) use ($query) {
            $rows = $this->loadFromTableRows($query, $database);
            $rows = $this->performJoins($query, $database, $rows);
            $rows = $this->performWhere($query, $rows);

            $isGrouped      = !empty($query->getGroupBy());
            $isImpliedGroup = false;

            foreach ($query->getAliasColumnMap() as $expression) {
                if ($expression instanceof Aggregate) {
                    $isImpliedGroup = true;
                    break;
                }
            }

            if ($isGrouped) {
                $compiledGroupings = $this->compileExpressions($query->getGroupBy());
                $rows              = $rows->groupBy(function (array $row) use ($compiledGroupings) {
                    $grouping = [];

                    foreach ($compiledGroupings as $compiledGrouping) {
                        $grouping[] = $compiledGrouping($row);
                    }

                    return $grouping;
                });
            } elseif ($isImpliedGroup) {
                $rows = $rows->groupBy(function () {
                    return 1;
                });
            }

            foreach ($query->getHaving() as $having) {
                $rows = $rows->where($this->compileExpression($having));
            }

            if (!$isImpliedGroup) {
                $rows = $this->performOrderBy($query, $rows);
            }

            $rows = $this->performLimitAndOffset($query, $rows);

            $aliasCompiledMap = $this->compileExpressions($query->getAliasColumnMap());
            $rows             = $rows->select(function ($rowOrGroup) use ($aliasCompiledMap) {
                $selected = [];

                foreach ($aliasCompiledMap as $alias => $selector) {
                    $selected[$alias] = $selector($rowOrGroup);
                }

                return $selected;
            });

            return $rows->asArray();
        };

        return new PhpCompiledQuery($compiledQuery);
    }

    protected function loadFromTableRows(Query $query, MockDatabase $database)
    {
        return $this->loadTableRows($database, $query->getTableName(), $query->getTableAlias());
    }

    protected function loadTableRows(MockDatabase $database, $table, $alias)
    {
        $rows = [];

        foreach ($database->getTable($table)->getRows() as $row) {
            $rows[] = [$alias => $row];
        }

        return new Collection($rows);
    }

    protected function performJoins(Query $query, MockDatabase $database, ICollection $rows)
    {
        foreach ($query->getJoins() as $join) {
            $joinedTable  = $join->getTable();
            $onConditions = $this->compileExpressions($join->getOn());
            $joinedRows   = $this->loadTableRows($database, $joinedTable->getName(), $join->getAlias());

            switch ($join->getType()) {
                case Join::INNER:
                    $rows = $this->performJoin($rows, $joinedRows, $join->getAlias(), $joinedTable, $onConditions, false);
                    break;

                case Join::LEFT:
                    $rows = $this->performJoin($rows, $joinedRows, $join->getAlias(), $joinedTable, $onConditions, true);
                    break;

                case Join::RIGHT:
                    $rows = $this->performJoin($joinedRows, $rows, $join->getAlias(), $query->getTable(), $onConditions, true);
                    break;
            }
        }

        return $rows;
    }

    protected function performJoin(ICollection $rows, ICollection $joinedRows, $alias, Table $joinedTable, array $onConditions, $isOuter)
    {
        $joinWhere = $rows->join($joinedRows)
                ->on(function ($row, $joined) use ($onConditions) {
                    foreach ($onConditions as $on) {
                        if (!$on($row + $joined)) {
                            return false;
                        }
                    }

                    return true;
                });

        if ($isOuter) {
            $joinWhere = $joinWhere->withDefault([$alias => array_fill_keys($joinedTable->getColumnNames(), null)]);
        }


        return $joinWhere->to(function ($row, $joined) {
            return $row + $joined;
        });
    }

    protected function performWhere(Query $query, ICollection $rows)
    {
        foreach ($query->getWhere() as $where) {
            $rows = $rows->where($this->compileExpression($where));
        }

        return $rows;
    }

    protected function performOrderBy(Query $query, ICollection $rows)
    {
        $orderings = $query->getOrderings();
        if ($orderings) {
            /** @var Ordering $first */
            $first = array_pop($orderings);
            $rows  = $rows->orderBy(
                    $this->compileExpression($first->getExpression()),
                    $first->getMode() === Ordering::ASC ? Direction::ASCENDING : Direction::DESCENDING
            );

            foreach ($orderings as $ordering) {
                $rows = $rows->thenBy(
                        $this->compileExpression($ordering->getExpression()),
                        $ordering->getMode() === Ordering::ASC ? Direction::ASCENDING : Direction::DESCENDING
                );
            }
        }

        return $rows;
    }

    protected function performLimitAndOffset(Query $query, ICollection $rows)
    {
        if ($query->getOffset() === 0 && $query->getLimit() === null) {
            return $rows;
        }

        return $rows->slice($query->getOffset(), $query->getLimit());
    }

    /**
     * @inheritDoc
     */
    public function compileUpdate(Update $query)
    {
        $compiledQuery = function (MockDatabase $database) use ($query) {
            $allRows = $rows = $this->loadFromTableRows($query, $database);
            $rows    = $this->performJoins($query, $database, $rows);
            $rows    = $this->performWhere($query, $rows);
            $rows    = $this->performOrderBy($query, $rows);
            $rows    = $this->performLimitAndOffset($query, $rows);

            $table            = $query->getTableAlias();
            $primaryKey       = $query->getTable()->getPrimaryKeyColumnName();
            $compiledSets     = $this->compileExpressions($query->getColumnSetMap());
            $updatedRowsCount = $rows->count();

            $updatedRows = $rows
                    ->indexBy(function (array $row) use ($table, $primaryKey) {
                        return $row[$table][$primaryKey];
                    })
                    ->select(function (array $row) use ($table, $compiledSets) {
                        foreach ($compiledSets as $column => $setter) {
                            $row[$table][$column] = $setter($row);
                        }

                        return $row[$table];
                    })
                    ->asArray();

            $newRows = $allRows->select(function (array $row) use ($updatedRows, $table, $primaryKey) {
                if (isset($updatedRows[$row[$table][$primaryKey]])) {
                    return $updatedRows[$row[$table][$primaryKey]];
                } else {
                    return $row[$table];
                }
            });

            $database->getTable($query->getTableName())->setRows($newRows->asArray());

            return $updatedRowsCount;
        };

        return new PhpCompiledQuery($compiledQuery);
    }

    /**
     * @inheritDoc
     */
    public function compileDelete(Delete $query)
    {
        $compiledQuery = function (MockDatabase $database) use ($query) {
            $allRows = $rows = $this->loadFromTableRows($query, $database);
            $rows    = $this->performJoins($query, $database, $rows);
            $rows    = $this->performWhere($query, $rows);
            $rows    = $this->performOrderBy($query, $rows);
            $rows    = $this->performLimitAndOffset($query, $rows);

            $table       = $query->getTableAlias();
            $primaryKey  = $query->getTable()->getPrimaryKeyColumnName();
            $deletedRows = $rows->count();
            $idsToDelete = $rows
                    ->indexBy(function (array $row) use ($table, $primaryKey) {
                        return $primaryKey ? $row[$table][$primaryKey] : Identity::hash($row[$table]);
                    })
                    ->asArray();

            $newRows = $allRows
                    ->where(function (array $row) use ($table, $primaryKey, $idsToDelete) {
                        return !isset($idsToDelete[$primaryKey ? $row[$table][$primaryKey] : Identity::hash($row[$table])]);
                    })
                    ->select(function (array &$row) use ($table) {
                        return $row[$table];
                    });

            $database->getTable($query->getTableName())->setRows($newRows->asArray());

            return $deletedRows;
        };

        return new PhpCompiledQuery($compiledQuery);
    }

    protected function compileSelectQuery(Select $query, CompiledQueryBuilder $compiled)
    {

    }

    protected function compileUpdateQuery(Update $query, CompiledQueryBuilder $compiled)
    {

    }

    protected function compileDeleteQuery(Delete $query, CompiledQueryBuilder $compiled)
    {

    }
}