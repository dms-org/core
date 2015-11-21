<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query\Expression;

use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Boolean;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;

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
    private function __construct($operator, Expr $operand, Type $resultingType)
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
    public static function isNull(Expr $operand)
    {
        return new self(self::IS_NULL, $operand, new Boolean());
    }

    /**
     * @param Expr $operand
     *
     * @return UnaryOp
     */
    public static function isNotNull(Expr $operand)
    {
        return new self(self::IS_NOT_NULL, $operand, new Boolean());
    }

    /**
     * @param Expr $operand
     *
     * @return UnaryOp
     */
    public static function not(Expr $operand)
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
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return Expr
     */
    public function getOperand()
    {
        return $this->operand;
    }

    /**
     * @inheritDoc
     */
    public function getChildren()
    {
        return [$this->operand];
    }

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType()
    {
        return $this->resultingType;
    }
}