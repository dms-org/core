<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query\Clause;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The join query class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Join
{
    const INNER = 'inner';
    const LEFT = 'left';
    const RIGHT = 'right';

    /**
     * @var Table
     */
    private $table;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Expr[]
     */
    private $on = [];

    /**
     * Join constructor.
     *
     * @param string $type
     * @param Table  $table
     * @param string $alias
     * @param Expr[] $on
     */
    public function __construct(string $type, Table $table, string $alias, array $on)
    {
        InvalidArgumentException::verify(in_array($type, [self::INNER, self::LEFT, self::RIGHT]), 'on', 'Invalid join type \'%s\' given', $type);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'on', $on, Expr::class);

        $this->type  = $type;
        $this->table = $table;
        $this->alias = $alias;
        $this->on    = $on;
    }

    /**
     * @param Table  $table
     * @param string $alias
     * @param Expr[] $on
     *
     * @return Join
     */
    public static function inner(Table $table, string $alias, array $on) : Join
    {
        return new self(self::INNER, $table, $alias, $on);
    }

    /**
     * @param Table  $table
     * @param string $alias
     * @param Expr[] $on
     *
     * @return Join
     */
    public static function left(Table $table, string $alias, array $on) : Join
    {
        return new self(self::LEFT, $table, $alias, $on);
    }

    /**
     * @param Table  $table
     * @param string $alias
     * @param Expr[] $on
     *
     * @return Join
     */
    public static function right(Table $table, string $alias, array $on) : Join
    {
        return new self(self::RIGHT, $table, $alias, $on);
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
    public function getAlias() : string
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
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return Expr[]
     */
    public function getOn() : array
    {
        return $this->on;
    }
}