<?php declare(strict_types = 1);

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
    public function __construct(Expr $expression, string $mode)
    {
        $this->expression = $expression;
        $this->mode       = $mode;
    }

    /**
     * @return Expr
     */
    public function getExpression() : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        return $this->expression;
    }

    /**
     * @return string
     */
    public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * @return bool
     */
    public function isAsc() : bool
    {
        return $this->mode === self::ASC;
    }
}