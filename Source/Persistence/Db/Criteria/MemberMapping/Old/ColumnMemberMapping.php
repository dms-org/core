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
     * @param string $tableAlias
     *
     * @return Expr
     */
    protected function getExpressionInSelect(Select $select, $tableAlias)
    {
        return Expr::column($tableAlias, $this->column);
    }
}