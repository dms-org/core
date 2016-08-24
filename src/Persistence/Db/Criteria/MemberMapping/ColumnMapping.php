<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Column;

/**
 * The column mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnMapping extends MemberMapping
{
    /**
     * @var Column
     */
    protected $column;

    /**
     * @var callable|null
     */
    protected $phpToDbValueConverter;

    /**
     * ColumnMapping constructor.
     *
     * @param IEntityMapper $rootEntityMapper
     * @param array         $relationsToSubSelect
     * @param Column        $column
     * @param callable|null $phpToDbValueConverter
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect, Column $column, callable $phpToDbValueConverter = null)
    {
        parent::__construct($rootEntityMapper, $relationsToSubSelect);
        $this->column                = $column;
        $this->phpToDbValueConverter = $phpToDbValueConverter;
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, string $tableAlias, string $operator, $value) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        if ($this->phpToDbValueConverter) {
            $value = call_user_func($this->phpToDbValueConverter, $value);
        }

        return parent::getWhereConditionExpr($select, $tableAlias, $operator, $value);
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        return Expr::column($tableAlias, $this->column);
    }
}