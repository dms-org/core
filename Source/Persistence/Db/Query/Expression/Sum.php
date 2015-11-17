<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query\Expression;

use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The SUM(...) aggregate expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Sum extends ArgumentAggregate
{
    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType()
    {
        return $this->argument->getResultingType();
    }
}