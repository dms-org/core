<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query\Expression;

use Dms\Core\Persistence\Db\Schema\Type\Boolean;
use Dms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The where condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BinOp extends Expr
{
    // Bool
    const EQUAL = 'equal';
    const NOT_EQUAL = 'not-equal';
    const LESS_THAN = 'less-than';
    const LESS_THAN_OR_EQUAL = 'less-than-or-equal';
    const GREATER_THAN = 'greater-than';
    const GREATER_THAN_OR_EQUAL = 'greater-than-or-equal';
    const IN = 'in';
    const NOT_IN = 'not-in';
    const AND_ = 'and';
    const OR_ = 'or';
    const STR_CONTAINS = 'str-contains';
    const STR_CONTAINS_CASE_INSENSITIVE = 'str-contains-case-insensitive';

    // Math
    const ADD = 'add';
    const SUBTRACT = 'subtract';

    /**
     * @var Expr
     */
    private $left;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var Expr
     */
    private $right;

    /**
     * Condition constructor.
     *
     * @param Expr   $left
     * @param string $operator
     * @param Expr   $right
     */
    public function __construct(Expr $left, string $operator, Expr $right)
    {
        $this->left     = $left;
        $this->operator = $operator;
        $this->right    = $right;
    }

    /**
     * @return Expr
     */
    public function getLeft() : Expr
    {
        return $this->left;
    }

    /**
     * @return string
     */
    public function getOperator() : string
    {
        return $this->operator;
    }

    /**
     * @return Expr
     */
    public function getRight() : Expr
    {
        return $this->right;
    }

    /**
     * @inheritDoc
     */
    public function getChildren() : array
    {
        return [$this->left, $this->right];
    }

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType() : \Dms\Core\Persistence\Db\Schema\Type\Type
    {
        if (in_array($this->operator, [self::ADD, self::SUBTRACT])) {
            return $this->left->getResultingType();
        } else {
            return new Boolean();
        }
    }
}