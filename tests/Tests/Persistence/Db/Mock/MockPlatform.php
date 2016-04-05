<?php

namespace Dms\Core\Tests\Persistence\Db\Mock;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Persistence\Db\Platform\CompiledQuery;
use Dms\Core\Persistence\Db\Platform\CompiledQueryBuilder;
use Dms\Core\Persistence\Db\Platform\Platform;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Clause\Ordering;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Aggregate;
use Dms\Core\Persistence\Db\Query\Expression\BinOp;
use Dms\Core\Persistence\Db\Query\Expression\ColumnExpr;
use Dms\Core\Persistence\Db\Query\Expression\Count;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Expression\Parameter;
use Dms\Core\Persistence\Db\Query\Expression\SimpleAggregate;
use Dms\Core\Persistence\Db\Query\Expression\SubSelect;
use Dms\Core\Persistence\Db\Query\Expression\Tuple;
use Dms\Core\Persistence\Db\Query\Expression\UnaryOp;
use Dms\Core\Persistence\Db\Query\Query;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Schema\Table;
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
    protected function dateFormatString() : string
    {
        return 'Y-m-d';
    }

    /**
     * @return string
     */
    protected function dateTimeFormatString() : string
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * @return string
     */
    protected function timeFormatString() : string
    {
        return 'H:i:s';
    }

    /**
     * @inheritDoc
     */
    public function quoteIdentifier(string $value) : string
    {
        return '!!' . $value . '!!';
    }

    public function compilePreparedInsert(Table $table)
    {
        return new PhpPreparedCompiledQuery(function (MockDatabase $database, array $parameters) use ($table) {
            $table = $database->getTable($table->getName());

            $table->insert($parameters + $table->getStructure()->getNullColumnData());

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

    protected function compileExpressions(MockDatabase $database, array $exprs)
    {
        $compiled = [];

        foreach ($exprs as $key => $expr) {
            $compiled[$key] = $this->compileExpression($database, $expr);
        }

        return $compiled;
    }

    protected function compileExpression(MockDatabase $database, Expr $expr)
    {
        switch (true) {
            case $expr instanceof BinOp:
                return $this->compileBinOp($database, $expr);

            case $expr instanceof UnaryOp:
                return $this->compileUnaryOp($database, $expr);

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

            case $expr instanceof SimpleAggregate:
                return $this->compileSimpleAggregate($database, $expr);

            case $expr instanceof Parameter:
                $parameter = $this->mapValueToDbFormat($expr->getType(), $expr->getValue());

                return function ($row) use ($parameter) {
                    return $parameter;
                };

            case $expr instanceof Tuple:
                $compiled = $this->compileExpressions($database, $expr->getExpressions());

                return function ($row) use ($compiled) {
                    $values = [];
                    foreach ($compiled as $inner) {
                        $values[] = $inner($row);
                    }

                    return $values;
                };

            case $expr instanceof SubSelect:
                return $this->compileSubSelect($database, $expr);
        }
    }

    private function compileSubSelect(MockDatabase $database, SubSelect $expr)
    {
        $compiled = $this->compileSelect($expr->getSelect())->getCompiled();

        return function ($row) use ($compiled, $database) {
            $result = $compiled($database, $row);

            if (count($result) > 1) {
                throw InvalidArgumentException::format('Subselect returned value with more than one row, %d returned', count($result));
            }

            if (count($result) === 0) {
                return null;
            }

            $row = reset($result);

            if (count($row) !== 1) {
                throw InvalidArgumentException::format('Subselect returned row with other than one column, %d returned', count($row));
            }

            return reset($row);
        };
    }

    private function compileBinOp(MockDatabase $database, BinOp $expr)
    {
        $left  = $this->compileExpression($database, $expr->getLeft());
        $right = $this->compileExpression($database, $expr->getRight());

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
            case BinOp::ADD:
                return function ($row) use ($left, $right) {
                    return $left($row) + $right($row);
                };
            case BinOp::SUBTRACT:
                return function ($row) use ($left, $right) {
                    return $left($row) - $right($row);
                };
        }

        throw NotImplementedException::format('Unknown bin op %s', $expr->getOperator());
    }

    private function compileUnaryOp(MockDatabase $database, UnaryOp $expr)
    {
        $operand = $this->compileExpression($database, $expr->getOperand());

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

    private function compileSimpleAggregate(MockDatabase $database, SimpleAggregate $expr)
    {
        $argument = $this->compileExpression($database, $expr->getArgument());

        switch ($expr->getType()) {
            case SimpleAggregate::SUM:
                return function (ICollection $group) use ($argument) {
                    return $group->sum($argument);
                };

            case SimpleAggregate::AVG:
                return function (ICollection $group) use ($argument) {
                    return $group->average($argument);
                };

            case SimpleAggregate::MIN:
                return function (ICollection $group) use ($argument) {
                    return $group->minimum($argument);
                };

            case SimpleAggregate::MAX:
                return function (ICollection $group) use ($argument) {
                    return $group->maximum($argument);
                };
        }

        throw NotImplementedException::format('Unknown aggregate type %s', $expr->getType());
    }

    /**
     * @inheritDoc
     */
    public function compileSelect(Select $query) : CompiledQuery
    {
        $compiledQuery = function (MockDatabase $database, array $outerData = null) use ($query) {
            $rows = $this->loadFromTableRows($query, $database);

            if ($outerData) {
                $rows = $rows->select(function (array $row) use ($outerData) {
                    return $row + $outerData;
                });
            }

            $rows = $this->performJoins($query, $database, $rows);
            $rows = $this->performWhere($database, $query, $rows);

            $isGrouped      = !empty($query->getGroupBy());
            $isImpliedGroup = false;

            Expr::walkAll($query->getAliasColumnMap(), function (Expr $expression) use (&$isImpliedGroup) {
                if ($expression instanceof Aggregate) {
                    $isImpliedGroup = true;
                }
            });

            if ($isGrouped) {
                $compiledGroupings = $this->compileExpressions($database, $query->getGroupBy());
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
                $rows = $rows->where($this->compileExpression($database, $having));
            }

            if (!$isImpliedGroup) {
                $rows = $this->performOrderBy($database, $query, $rows);
            }

            $rows = $this->performLimitAndOffset($query, $rows);

            $aliasCompiledMap = $this->compileExpressions($database, $query->getAliasColumnMap());
            $rows             = new Collection($rows->asArray());

            if ($rows->isEmpty() && $isImpliedGroup) {
                $rows->addRange([new Collection()]);
            }

            $rows = $rows->select(function ($rowOrGroup) use ($aliasCompiledMap) {
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
            $onConditions = $this->compileExpressions($database, $join->getOn());
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

    protected function performWhere(MockDatabase $database, Query $query, ICollection $rows)
    {
        foreach ($query->getWhere() as $where) {
            $rows = $rows->where($this->compileExpression($database, $where));
        }

        return $rows;
    }

    protected function performOrderBy(MockDatabase $database, Query $query, ICollection $rows)
    {
        $orderings = $query->getOrderings();
        if ($orderings) {
            /** @var Ordering $first */
            $first = array_pop($orderings);
            $rows  = $rows->orderBy(
                $this->compileExpression($database, $first->getExpression()),
                $first->getMode() === Ordering::ASC ? Direction::ASCENDING : Direction::DESCENDING
            );

            foreach ($orderings as $ordering) {
                $rows = $rows->thenBy(
                    $this->compileExpression($database, $ordering->getExpression()),
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
    public function compileUpdate(Update $query) : CompiledQuery
    {
        $compiledQuery = function (MockDatabase $database) use ($query) {
            $allRows = $rows = $this->loadFromTableRows($query, $database);
            $rows    = $this->performJoins($query, $database, $rows);
            $rows    = $this->performWhere($database, $query, $rows);
            $rows    = $this->performOrderBy($database, $query, $rows);
            $rows    = $this->performLimitAndOffset($query, $rows);

            $table            = $query->getTableAlias();
            $primaryKey       = $query->getTable()->getPrimaryKeyColumnName();
            $compiledSets     = $this->compileExpressions($database, $query->getColumnSetMap());
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
    public function compileDelete(Delete $query) : CompiledQuery
    {
        $compiledQuery = function (MockDatabase $database) use ($query) {
            $allRows = $rows = $this->loadFromTableRows($query, $database);
            $rows    = $this->performJoins($query, $database, $rows);
            $rows    = $this->performWhere($database, $query, $rows);
            $rows    = $this->performOrderBy($database, $query, $rows);
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

    /**
     * @inheritDoc
     */
    public function compileResequenceOrderIndexColumn(ResequenceOrderIndexColumn $query) : CompiledQuery
    {
        $compiledQuery = function (MockDatabase $database) use ($query) {
            $tableName  = $query->getTable()->getName();
            $columnName = $query->getColumn()->getName();
            $rows       = $this->loadTableRows($database, $tableName, $tableName);

            if ($query->hasWhereCondition()) {
                $rows = $rows->where($this->compileExpression($database, $query->getWhereCondition()));
            }

            if ($query->hasGroupingColumn()) {
                $groupColumnName = $query->getGroupingColumn()->getName();

                $rows = $rows->groupBy(function (array $row) use ($tableName, $groupColumnName) {
                    return $row[$tableName][$groupColumnName];
                });
            } else {
                $rows = $rows->groupBy(function () {
                    return 1;
                });
            }

            $rows = $rows->selectMany(function (ICollection $group) use ($tableName, $columnName) {
                $orderIndex = 1;

                $group
                    ->orderByAscending(function (array $row) use ($tableName, $columnName) {
                        return $row[$tableName][$columnName];
                    })
                    ->apply(function (array &$row) use ($tableName, $columnName, &$orderIndex) {
                        $row[$tableName][$columnName] = $orderIndex++;
                    });

                return $group;
            });

            $newRows = $rows
                ->select(function (array &$row) use ($tableName) {
                    return $row[$tableName];
                });

            $database->getTable($tableName)->setRows($newRows->asArray());
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

    protected function compileResequenceOrderIndexColumnQuery(ResequenceOrderIndexColumn $query, CompiledQueryBuilder $compiled)
    {

    }
}