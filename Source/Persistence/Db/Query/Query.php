<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Ordering;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\BinOp;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Parameter;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The db query base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Query implements IQuery
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var Join[]
     */
    private $joins = [];

    /**
     * @var Expr[]
     */
    private $where = [];

    /**
     * @var Ordering[]
     */
    private $orderings = [];

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var int|null
     */
    private $limit;

    /**
     * Query constructor.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->alias = $table->getName();
    }

    /**
     * Copies the query from the other query
     *
     * @param Query $otherQuery
     *
     * @return static
     */
    public static function copyFrom(Query $otherQuery)
    {
        $self            = new static($otherQuery->table);
        $self->joins     = $otherQuery->joins;
        $self->where     = $otherQuery->where;
        $self->orderings = $otherQuery->orderings;
        $self->offset    = $otherQuery->offset;
        $self->limit     = $otherQuery->limit;

        return $self;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        $aliases = [$this->alias];

        foreach ($this->joins as $join) {
            $aliases[] = $join->getAlias();
        }

        return $aliases;
    }

    /**
     * @param string $alias
     *
     * @return Table
     * @throws InvalidArgumentException
     */
    public function getTableFromAlias($alias)
    {
        if ($this->alias === $alias) {
            return $this->table;
        }

        foreach ($this->joins as $join) {
            if ($join->getAlias() === $alias) {
                return $join->getTable();
            }
        }

        throw InvalidArgumentException::format(
                'Invalid alias supplied to %s: expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues($this->getAliases()), $alias
        );
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function generateUniqueAliasFor($tableName)
    {
        $aliases = array_flip($this->getTakenAliases());
        if (!isset($aliases[$tableName])) {
            return $tableName;
        }

        $count = 1;
        do {
            $newTableName = $tableName . $count;
            $count++;
        } while (in_array($newTableName, $aliases, true));

        return $newTableName;
    }

    /**
     * @return string[]
     */
    protected function getTakenAliases()
    {
        return $this->getAliases();
    }

    /**
     * @param Table $table
     *
     * @return static
     */
    public static function from(Table $table)
    {
        return new static($table);
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table->getName();
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function isTableAliased()
    {
        return $this->alias !== $this->getTableName();
    }

    /**
     * @param string $alias
     *
     * @return static
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param Table $table
     *
     * @return static
     */
    public function setTable(Table $table)
    {
        $this->table = $table;
        $this->alias = $this->generateUniqueAliasFor($table->getName());

        return $this;
    }

    /**
     * @return Clause\Join[]
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @param Join $join
     *
     * @return static
     */
    public function join(Join $join)
    {
        $this->joins[] = $join;

        return $this;
    }

    /**
     * @param Join $join
     *
     * @return static
     */
    public function prependJoin(Join $join)
    {
        array_unshift($this->joins, $join);

        return $this;
    }

    /**
     * @return Expr[]
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param Expr $where
     *
     * @return static
     */
    public function where(Expr $where)
    {
        if ($where instanceof Parameter && $where == Expr::true()) {
            return $this;
        }

        if ($where instanceof BinOp && $where->getOperator() === BinOp::AND_) {
            $this->where($where->getLeft());
            $this->where($where->getRight());
        } else {
            $this->where[] = $where;
        }

        return $this;
    }

    /**
     * @return Ordering[]
     */
    public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * @param Ordering $ordering
     *
     * @return static
     */
    public function orderBy(Ordering $ordering)
    {
        $this->orderings[] = $ordering;

        return $this;
    }

    /**
     * @param Expr $expr
     *
     * @return static
     */
    public function orderByAsc(Expr $expr)
    {
        return $this->orderBy(new Ordering($expr, Ordering::ASC));
    }

    /**
     * @param Expr $expr
     *
     * @return static
     */
    public function orderByDesc(Expr $expr)
    {
        return $this->orderBy(new Ordering($expr, Ordering::DESC));
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return static
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int
     */
    public function hasLimit()
    {
        return $this->limit !== null;
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     *
     * @return static
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLimitOrOffset()
    {
        return $this->hasLimit() || $this->offset !== 0;
    }

    /**
     * Clones the query
     *
     * @return static
     */
    public function copy()
    {
        return clone $this;
    }
}