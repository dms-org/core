<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query\Expression;

use Dms\Core\Persistence\Db\Schema\Type\Boolean;
use Dms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The unary op expression
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UnaryOp extends Expr
{
    const NOT = 'not';
    const IS_NULL = 'is-null';
    const IS_NOT_NULL = 'is-not-null';

    /**
     * @var string
     */
    private $operator;

    /**
     * @var Expr
     */
    private $operand;

    /**
     * @var Type
     */
    private $resultingType;

    /**
     * UnaryOp constructor.
     *
     * @param string $operator
     * @param Expr   $operand
     * @param Type   $resultingType
     */
    private function __construct(string $operator, Expr $operand, Type $resultingType)
    {
        $this->operator      = $operator;
        $this->operand       = $operand;
        $this->resultingType = $resultingType;
    }

    /**
     * @param Expr $operand
     *
     * @return UnaryOp
     */
    public static function isNull(Expr $operand) : UnaryOp
    {
        return new self(self::IS_NULL, $operand, new Boolean());
    }

    /**
     * @param Expr $operand
     *
     * @return UnaryOp
     */
    public static function isNotNull(Expr $operand) : UnaryOp
    {
        return new self(self::IS_NOT_NULL, $operand, new Boolean());
    }

    /**
     * @param Expr $operand
     *
     * @return UnaryOp
     */
    public static function not(Expr $operand) : Expr
    {
        if ($operand instanceof self) {
            if ($operand->getOperator() === self::IS_NULL) {
                return self::isNotNull($operand->getOperand());
            } elseif ($operand->getOperator() === self::IS_NOT_NULL) {
                return self::isNull($operand->getOperand());
            } elseif ($operand->getOperator() === self::NOT) {
                return $operand->getOperand();
            }
        }

        return new self(self::NOT, $operand, new Boolean());
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
    public function getOperand() : Expr
    {
        return $this->operand;
    }

    /**
     * @inheritDoc
     */
    public function getChildren() : array
    {
        return [$this->operand];
    }

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType() : \Dms\Core\Persistence\Db\Schema\Type\Type
    {
        return $this->resultingType;
    }
}