<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Query\Expression;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;

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
     * @var int
     */
    protected $subSelectCounter = 0;

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
    public function compileExpressions(QueryBuilder $queryBuilder, array $expressions) : array
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
     * @param bool         $subSelectAlias
     *
     * @return array|string
     * @throws InvalidArgumentException
     */
    public function compileExpression(QueryBuilder $queryBuilder, Expr $expr, bool $subSelectAlias = false)
    {
        switch (true) {
            case $expr instanceof Expression\Count:
                return $this->doctrinePlatform->getCountExpression('*');

            case $expr instanceof Expression\SimpleAggregate:
                return $this->compileSimpleAggregate($queryBuilder, $expr);

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

            case $expr instanceof Expression\SubSelect:
                return $this->compileSubSelect($queryBuilder, $expr, $subSelectAlias);
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
                return $right === [] ? $this->compileExpression($queryBuilder, Expr::false()) : $expressionBuilder->in($left, $right);

            case Expression\BinOp::NOT_IN:
                return $right === [] ? $this->compileExpression($queryBuilder, Expr::true()) : $expressionBuilder->notIn($left, $right);

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

            case Expression\BinOp::ADD:
                return '(' . $left . ' + ' . $right . ')';

            case Expression\BinOp::SUBTRACT:
                return '(' . $left . ' - ' . $right . ')';

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

    private function compileSimpleAggregate(QueryBuilder $queryBuilder, Expression\SimpleAggregate $expr)
    {
        $argument = $this->compileExpression($queryBuilder, $expr->getArgument());

        switch ($expr->getType()) {
            case Expression\SimpleAggregate::SUM:
                return $this->doctrinePlatform->getSumExpression($argument);

            case Expression\SimpleAggregate::AVG:
                return $this->doctrinePlatform->getAvgExpression($argument);

            case Expression\SimpleAggregate::MAX:
                return $this->doctrinePlatform->getMaxExpression($argument);

            case Expression\SimpleAggregate::MIN:
                return $this->doctrinePlatform->getMinExpression($argument);
        }

        throw InvalidArgumentException::format('Unknown aggregate type: ' . $expr->getType());
    }

    private function compileSubSelect(QueryBuilder $queryBuilder, Expression\SubSelect $expr, bool $subSelectAlias)
    {
        $subSelect = $this->platform->compileSelect($expr->getSelect());

        foreach ($subSelect->getParameters() as $parameter) {
            $queryBuilder->createPositionalParameter($parameter);
        }

        if ($subSelectAlias) {
            $subSelectAlias = $this->doctrinePlatform->quoteSingleIdentifier('__sub_select_' . $this->subSelectCounter++);

            return '(' . $subSelect->getSql() . ') AS ' . $subSelectAlias;
        } else {
            return '(' . $subSelect->getSql() . ')';
        }
    }
}