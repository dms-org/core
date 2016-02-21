<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query\Expression;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Type;
use Dms\Core\Util\Debug;

/**
 * The query expression base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Expr
{
    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    abstract public function getResultingType() : \Dms\Core\Persistence\Db\Schema\Type\Type;

    /**
     * Gets an array of the expressions contained within this expression.
     *
     * @return Expr[]
     */
    abstract public function getChildren() : array;

    /**
     * Walks the current expression and the children expressions.
     *
     * @param callable $callback
     *
     * @return void
     */
    final public function walk(callable $callback)
    {
        $callback($this);

        foreach ($this->getChildren() as $child) {
            $child->walk($callback);
        }
    }

    /**
     * @param Expr[]   $expressions
     * @param callable $callback
     *
     * @return void
     */
    public static function walkAll(array $expressions, callable $callback)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'expressions', $expressions, __CLASS__);

        foreach ($expressions as $expression) {
            $expression->walk($callback);
        }
    }

    /**
     * @param string $table
     * @param Column $column
     *
     * @return ColumnExpr
     */
    public static function column(string $table, Column $column) : ColumnExpr
    {
        return new ColumnExpr($table, $column);
    }

    /**
     * @param Table  $table
     * @param string $columnName
     *
     * @return ColumnExpr
     * @throws InvalidArgumentException
     */
    public static function tableColumn(Table $table, string $columnName) : ColumnExpr
    {
        if (!$table->hasColumn($columnName)) {
            throw InvalidArgumentException::format(
                    'Invalid table column name for table \'%s\': expecting one of (%s), \'%s\' given',
                    $table->getName(), Debug::formatValues($table->getColumnNames()), $columnName
            );
        }

        return new ColumnExpr($table->getName(), $table->findColumn($columnName));
    }

    /**
     * @param Table $table
     *
     * @return ColumnExpr
     */
    public static function primaryKey(Table $table) : ColumnExpr
    {
        return new ColumnExpr($table->getName(), $table->getPrimaryKeyColumn());
    }

    /**
     * @param int|null $value
     *
     * @return Parameter
     */
    public static function idParam($value) : Parameter
    {
        return new Parameter(Integer::normal()->nullable(), $value);
    }

    /**
     * @param Type|null $type
     * @param mixed     $value
     *
     * @return Parameter
     */
    public static function param(Type $type = null, $value) : Parameter
    {
        return new Parameter($type ?? Type::fromValue($value), $value);
    }

    /**
     * @return Parameter
     */
    public static function true() : Parameter
    {
        return self::param(Integer::tiny(), 1);
    }

    /**
     * @return Parameter
     */
    public static function false() : Parameter
    {
        return self::param(Integer::tiny(), 0);
    }

    /**
     * @param Expr[] $expressions
     *
     * @return Tuple
     */
    public static function tuple(array $expressions) : Tuple
    {
        return new Tuple($expressions);
    }

    /**
     * @param Type|null $type
     * @param array     $params
     *
     * @return Tuple
     */
    public static function tupleParams(Type $type = null, array $params) : Tuple
    {
        $expressions = [];

        foreach ($params as $param) {
            $expressions[] = new Parameter($type ?? Type::fromValue($param), $param);
        }

        return new Tuple($expressions);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function equal(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::EQUAL, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function notEqual(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::NOT_EQUAL, $right);
    }


    /**
     * @param Expr  $left
     * @param Tuple $right
     *
     * @return BinOp
     */
    public static function in(Expr $left, Tuple $right) : BinOp
    {
        return new BinOp($left, BinOp::IN, $right);
    }

    /**
     * @param Expr  $left
     * @param Tuple $right
     *
     * @return BinOp
     */
    public static function notIn(Expr $left, Tuple $right) : BinOp
    {
        return new BinOp($left, BinOp::NOT_IN, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function and_(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::AND_, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function or_(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::OR_, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function greaterThan(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::GREATER_THAN, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function greaterThanOrEqual(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::GREATER_THAN_OR_EQUAL, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function lessThan(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::LESS_THAN, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function lessThanOrEqual(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::LESS_THAN_OR_EQUAL, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function strContains(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::STR_CONTAINS, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function strContainsCaseInsensitive(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::STR_CONTAINS_CASE_INSENSITIVE, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function add(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::ADD, $right);
    }

    /**
     * @param Expr $left
     * @param Expr $right
     *
     * @return BinOp
     */
    public static function subtract(Expr $left, Expr $right) : BinOp
    {
        return new BinOp($left, BinOp::SUBTRACT, $right);
    }

    /**
     * @param Expr $operand
     *
     * @return UnaryOp
     */
    public static function isNull(Expr $operand) : UnaryOp
    {
        return UnaryOp::isNull($operand);
    }

    /**
     * @param Expr $operand
     *
     * @return UnaryOp
     */
    public static function isNotNull(Expr $operand) : UnaryOp
    {
        return UnaryOp::isNotNull($operand);
    }

    /**
     * @param Expr $operand
     *
     * @return Expr
     */
    public static function not(Expr $operand) : Expr
    {
        if ($operand instanceof BinOp && $operand->getOperator() === BinOp::EQUAL) {
            return self::notEqual($operand->getLeft(), $operand->getRight());
        }

        return UnaryOp::not($operand);
    }

    /**
     * @param Expr[] $expressions
     *
     * @return Expr
     * @throws InvalidArgumentException
     */
    public static function compoundOr(array $expressions) : Expr
    {
        return self::compoundBinOp($expressions, BinOp::OR_);
    }

    /**
     * @param Expr[] $expressions
     *
     * @return Expr
     * @throws InvalidArgumentException
     */
    public static function compoundAnd(array $expressions) : Expr
    {
        return self::compoundBinOp($expressions, BinOp::AND_);
    }

    private static function compoundBinOp(array $expressions, $operator)
    {
        if (empty($expressions)) {
            throw InvalidArgumentException::format('Invalid call to %s: expressions cannot be empty', __METHOD__);
        }

        $compound = array_shift($expressions);

        while ($expression = array_shift($expressions)) {
            $compound = new BinOp($compound, $operator, $expression);
        }

        return $compound;
    }


    /**
     * @return Count
     */
    public static function count() : Count
    {
        return new Count();
    }

    /**
     * @param Expr $argument
     *
     * @return SimpleAggregate
     */
    public static function max(Expr $argument) : SimpleAggregate
    {
        return new SimpleAggregate(SimpleAggregate::MAX, $argument);
    }

    /**
     * @param Expr $argument
     *
     * @return SimpleAggregate
     */
    public static function min(Expr $argument) : SimpleAggregate
    {
        return new SimpleAggregate(SimpleAggregate::MIN, $argument);
    }

    /**
     * @param Expr $argument
     *
     * @return SimpleAggregate
     */
    public static function avg(Expr $argument) : SimpleAggregate
    {
        return new SimpleAggregate(SimpleAggregate::AVG, $argument);
    }

    /**
     * @param Expr $argument
     *
     * @return SimpleAggregate
     */
    public static function sum(Expr $argument) : SimpleAggregate
    {
        return new SimpleAggregate(SimpleAggregate::SUM, $argument);
    }

    /**
     * @param Select $select
     *
     * @return SubSelect
     */
    public static function subSelect(Select $select) : SubSelect
    {
        return new SubSelect($select);
    }
}