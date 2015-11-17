<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\BinOp;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

/**
 * The column member mapping class.
 *
 * For members that map to a single column.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnMemberMapping extends MemberMapping
{
    /**
     * @var Column
     */
    protected $column;

    /**
     * @inheritDoc
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $nestedRelations, Column $column)
    {
        parent::__construct($rootEntityMapper, $nestedRelations);
        $this->column = $column;
    }

    /**
     * @return Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param Select $select
     * @param string $operator
     * @param mixed  $value
     *
     * @see ConditionOperator
     *
     * @return void
     * @throws InvalidOperationException
     */
    protected function addWhereConditionToSelect(Select $select, $operator, $value)
    {
        if (empty($this->nestedRelations)) {
            $table = $select->getAliasFor($this->rootEntityMapper->getPrimaryTable()->getName());

            $select->where(new BinOp(
                    Expr::column($table, $this->column),
                    $operator,
                    Expr::param($this->column->getType(), $value)
            ));
        }
    }

    /**
     * @param Select $select
     * @param bool   $isAsc
     *
     * @return void
     * @throws InvalidOperationException
     */
    protected function addOrderByToSelect(Select $select, $isAsc)
    {
        // TODO: Implement addOrderByToSelect() method.
    }

    /**
     * @param Select $select
     * @param string $alias
     *
     * @return void
     * @throws InvalidOperationException
     */
    protected function addSelectColumn(Select $select, $alias)
    {
        // TODO: Implement addSelectColumn() method.
    }
}