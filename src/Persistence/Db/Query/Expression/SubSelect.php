<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query\Expression;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Type\Type;
use Dms\Core\Util\Debug;

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
    public function getSelect() : \Dms\Core\Persistence\Db\Query\Select
    {
        return $this->select;
    }

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType() : \Dms\Core\Persistence\Db\Schema\Type\Type
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
    public function getChildren() : array
    {
        return [];
    }
}