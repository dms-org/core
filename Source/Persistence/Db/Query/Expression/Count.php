<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query\Expression;

use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The COUNT(*) expression.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Count extends Aggregate
{

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType()
    {
        return Integer::normal();
    }
}