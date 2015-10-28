<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query\Expression;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The where condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Tuple extends Expr
{
    /**
     * @var Expr[]
     */
    private $expressions;

    /**
     * Tuple constructor.
     *
     * @param Expr[] $expressions
     */
    public function __construct(array $expressions)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'expressions', $expressions, Expr::class);
        $this->expressions = $expressions;
    }

    /**
     * @return Expr[]
     */
    public function getExpressions()
    {
        return $this->expressions;
    }

    /**
     * @inheritDoc
     */
    public function getChildren()
    {
        return $this->expressions;
    }

    /**
     * Gets the resulting type of the expression
     * @return Type
     * @throws InvalidOperationException
     */
    public function getResultingType()
    {
        throw InvalidOperationException::methodCall(__METHOD__, 'Tuple cannot be used a standalone expression');
    }
}