<?php

namespace Dms\Core\Persistence\Db\Query\Clause;

use Dms\Core\Persistence\Db\Query\Expression\Expr;

/**
 * The ordering clause class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Ordering
{
    const ASC = 'asc';
    const DESC = 'desc';

    /**
     * @var Expr
     */
    private $expression;

    /**
     * @var string
     */
    private $mode;

    /**
     * Ordering constructor.
     *
     * @param Expr $expression
     * @param string     $mode
     */
    public function __construct(Expr $expression, $mode)
    {
        $this->expression = $expression;
        $this->mode       = $mode;
    }

    /**
     * @return Expr
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return bool
     */
    public function isAsc()
    {
        return $this->mode === self::ASC;
    }
}