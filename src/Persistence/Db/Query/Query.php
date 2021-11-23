<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Clause\Ordering;
use Dms\Core\Persistence\Db\Query\Expression\BinOp;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Expression\Parameter;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Util\Debug;

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
        $self->alias     = $otherQuery->alias;
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
    public function getAliases() : array
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
    public function getTableFromAlias(string $alias) : \Dms\Core\Persistence\Db\Schema\Table
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
    public function generateUniqueAliasFor(string $tableName) : string
    {
        $aliases = array_flip($this->getTakenAliases());
        if (!isset($aliases[$tableName])) {
            return $tableName;
        }

        $count = 1;
        do {
            $newTableName = $tableName . $count;
            $count++;
        } while (isset($aliases[$newTableName]));

        return $newTableName;
    }

    /**
     * @return string[]
     */
    protected function getTakenAliases() : array
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
    public function getTable() : \Dms\Core\Persistence\Db\Schema\Table
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getTableName() : string
    {
        return $this->table->getName();
    }

    /**
     * @return string
     */
    public function getTableAlias() : string
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function isTableAliased() : bool
    {
        return $this->alias !== $this->getTableName();
    }

    /**
     * @param string $alias
     *
     * @return static
     */
    public function setAlias(string $alias)
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
    public function getJoins() : array
    {
        return $this->joins;
    }

    /**
     * @param Join $join
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function join(Join $join)
    {
        $takenAliases = $this->getTakenAliases();

        if (in_array($join->getAlias(), $takenAliases, true)) {
            throw InvalidArgumentException::format(
                'Invalid join supplied to %s::%s: alias \'%s\' is already taken, taken aliases are (%s)',
                get_class($this), __FUNCTION__, $join->getAlias(), Debug::formatValues($takenAliases)
            );
        }


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
    public function getWhere() : array
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
    public function getOrderings() : array
    {
        return $this->orderings;
    }

    /**
     * @return static
     */
    public function clearOrderings()
    {
        $this->orderings = [];
        return $this;
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
    public function getOffset() : int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return static
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLimit() : bool
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
    public function limit(int $limit = null)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLimitOrOffset() : bool
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