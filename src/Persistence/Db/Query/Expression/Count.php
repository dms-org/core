<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query\Expression;

use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The COUNT(*) expression.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Count extends Aggregate
{
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
    public function getResultingType() : \Dms\Core\Persistence\Db\Schema\Type\Type
    {
        return Integer::normal();
    }
}