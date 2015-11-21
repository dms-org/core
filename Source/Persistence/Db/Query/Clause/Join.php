<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query\Clause;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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
    public function __construct($type, Table $table, $alias, array $on)
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
    public static function inner(Table $table, $alias, array $on)
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
    public static function left(Table $table, $alias, array $on)
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
    public static function right(Table $table, $alias, array $on)
    {
        return new self(self::RIGHT, $table, $alias, $on);
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
    public function getAlias()
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Expr[]
     */
    public function getOn()
    {
        return $this->on;
    }
}