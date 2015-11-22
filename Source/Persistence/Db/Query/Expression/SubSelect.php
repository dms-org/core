<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query\Expression;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The sub-select expression class class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubSelect extends Expr
{
    /**
     * @var Select
     */
    private $select;

    /**
     * SubSelect constructor.
     *
     * @param Select $select
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Select $select)
    {
        if (count($select->getAliasColumnMap()) > 1) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: select can only contain a single column, (%s) given',
                    __METHOD__, Debug::formatValues(array_keys($select->getAliasColumnMap()))
            );
        }

        $this->select = $select;
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType()
    {
        $selectedExpressions = $this->select->getAliasColumnMap();
        /** @var Expr $selectExpression */
        $selectExpression    = reset($selectedExpressions);

        return $selectExpression->getResultingType();
    }

    /**
     * Gets an array of the expressions contained within this expression.
     *
     * @return Expr[]
     */
    public function getChildren()
    {
        return [];
    }
}