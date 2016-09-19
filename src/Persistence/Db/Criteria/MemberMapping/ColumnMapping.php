<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Persistence\Db\Mapping\Hierarchy\IObjectMapping;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
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
     * @param IEntityMapper    $rootEntityMapper
     * @param IObjectMapping[] $subclassObjectMappings
     * @param IRelation[]      $relationsToSubSelect
     * @param Column           $column
     * @param callable|null    $phpToDbValueConverter
     */
    public function __construct(
        IEntityMapper $rootEntityMapper,
        array $subclassObjectMappings,
        array $relationsToSubSelect,
        Column $column,
        callable $phpToDbValueConverter = null
    ) {
        parent::__construct($rootEntityMapper, $subclassObjectMappings, $relationsToSubSelect);
        $this->column                = $column;
        $this->phpToDbValueConverter = $phpToDbValueConverter;
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, string $tableAlias, string $operator, $value) : Expr
    {
        if ($this->phpToDbValueConverter) {
            $value = call_user_func($this->phpToDbValueConverter, $value);
        }

        return parent::getWhereConditionExpr($select, $tableAlias, $operator, $value);
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : Expr
    {
        return Expr::column($tableAlias, $this->column);
    }
}