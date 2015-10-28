<?php

namespace Iddigital\Cms\Core\Persistence\Db\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;

/**
 * The doctrine expression compiler class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrineExpressionCompiler
{
    /**
     * @var DoctrinePlatform
     */
    protected $platform;

    /**
     * @var AbstractPlatform
     */
    protected $doctrinePlatform;

    /**
     * DoctrineExpressionCompiler constructor.
     *
     * @param DoctrinePlatform $platform
     */
    public function __construct(DoctrinePlatform $platform)
    {
        $this->platform         = $platform;
        $this->doctrinePlatform = $platform->getDoctrinePlatform();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Expr[]       $expressions
     *
     * @return array
     */
    public function compileExpressions(QueryBuilder $queryBuilder, array $expressions)
    {
        $compiled = [];

        foreach ($expressions as $expr) {
            $compiled[] = $this->compileExpression($queryBuilder, $expr);
        }

        return $compiled;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Expr         $expr
     *
     * @return array|string
     * @throws InvalidArgumentException
     */
    public function compileExpression(QueryBuilder $queryBuilder, Expr $expr)
    {
        switch (true) {
            case $expr instanceof Expression\Count:
                return $this->doctrinePlatform->getCountExpression('*');

            case $expr instanceof Expression\Max:
                return $this->doctrinePlatform->getMaxExpression(
                        $this->compileExpression($queryBuilder, $expr->getArgument())
                );

            case $expr instanceof Expression\BinOp:
                return (string)$this->compileBinOp($queryBuilder, $expr);

            case $expr instanceof Expression\ColumnExpr:
                return ''
                . $this->doctrinePlatform->quoteSingleIdentifier($expr->getTable())
                . '.'
                . $this->doctrinePlatform->quoteSingleIdentifier($expr->getName());

            case $expr instanceof Expression\Parameter:
                return $queryBuilder->createPositionalParameter($this->platform->mapValueToDbFormat($expr->getType(), $expr->getValue()));

            case $expr instanceof Expression\Tuple:
                return $this->compileExpressions($queryBuilder, $expr->getExpressions());

            case $expr instanceof Expression\UnaryOp:
                return $this->compileUnaryOp($queryBuilder, $expr);
        }

        throw InvalidArgumentException::format('Unknown expression type: ' . get_class($expr));
    }

    private function compileBinOp(QueryBuilder $queryBuilder, Expression\BinOp $expr)
    {
        $left  = $this->compileExpression($queryBuilder, $expr->getLeft());
        $right = $this->compileExpression($queryBuilder, $expr->getRight());

        $expressionBuilder = $queryBuilder->expr();

        switch ($expr->getOperator()) {
            case Expression\BinOp::EQUAL:
                return $expressionBuilder->eq($left, $right);

            case Expression\BinOp::NOT_EQUAL:
                return $expressionBuilder->neq($left, $right);

            case Expression\BinOp::LESS_THAN:
                return $expressionBuilder->lt($left, $right);

            case Expression\BinOp::LESS_THAN_OR_EQUAL:
                return $expressionBuilder->lte($left, $right);

            case Expression\BinOp::GREATER_THAN:
                return $expressionBuilder->gt($left, $right);

            case Expression\BinOp::GREATER_THAN_OR_EQUAL:
                return $expressionBuilder->gte($left, $right);

            case Expression\BinOp::IN:
                return $expressionBuilder->in($left, $right);

            case Expression\BinOp::NOT_IN:
                return $expressionBuilder->notIn($left, $right);

            case Expression\BinOp::AND_:
                return $expressionBuilder->andX($left, $right);

            case Expression\BinOp::OR_:
                return $expressionBuilder->orX($left, $right);

            case Expression\BinOp::STR_CONTAINS:
                return $expressionBuilder->gt($this->doctrinePlatform->getLocateExpression($left, $right), 0);

            case Expression\BinOp::STR_CONTAINS_CASE_INSENSITIVE:
                return $expressionBuilder->gt(
                        $this->doctrinePlatform->getLocateExpression(
                                $this->doctrinePlatform->getUpperExpression($left),
                                $this->doctrinePlatform->getUpperExpression($right)
                        ),
                        0
                );

        }

        throw InvalidArgumentException::format('Unknown binary operator: ' . $expr->getOperator());
    }

    private function compileUnaryOp(QueryBuilder $queryBuilder, Expression\UnaryOp $expr)
    {
        $operand = $this->compileExpression($queryBuilder, $expr->getOperand());

        switch ($expr->getOperator()) {
            case Expression\UnaryOp::NOT:
                return $this->doctrinePlatform->getNotExpression($operand);

            case Expression\UnaryOp::IS_NULL:
                return $this->doctrinePlatform->getIsNullExpression($operand);

            case Expression\UnaryOp::IS_NOT_NULL:
                return $this->doctrinePlatform->getIsNotNullExpression($operand);
        }

        throw InvalidArgumentException::format('Unknown unary operator: ' . $expr->getOperator());
    }
}