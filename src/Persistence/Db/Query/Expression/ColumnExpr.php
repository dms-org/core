<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query\Expression;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The column expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnExpr extends Expr
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var Column
     */
    private $column;

    /**
     * ColumnExpr constructor.
     *
     * @param string $table
     * @param Column $column
     */
    public function __construct(string $table, Column $column)
    {
        InvalidArgumentException::verify(is_string($table), 'Table must be string, %s given', gettype($table));

        $this->table  = $table;
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getTable() : string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->column->getName();
    }

    /**
     * @return Column
     */
    public function getColumn() : Column
    {
        return $this->column;
    }

    /**
     * Gets an array of the expressions contained within this expression.
     *
     * @return Expr[]
     */
    public function getChildren() : array
    {
        return [];
    }

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType() : Type
    {
        return $this->column->getType();
    }
}