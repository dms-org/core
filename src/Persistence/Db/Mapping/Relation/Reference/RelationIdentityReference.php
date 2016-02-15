<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Column;

/**
 * The relation identity reference base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationIdentityReference extends RelationReference
{
    /**
     * @var Column
     */
    protected $primaryKeyColumn;

    /**
     * @param IEntityMapper $mapper
     * @param string|null   $bidirectionalRelationProperty
     */
    public function __construct(IEntityMapper $mapper, string $bidirectionalRelationProperty = null)
    {
        parent::__construct($mapper, $bidirectionalRelationProperty);
        $this->primaryKeyColumn = $this->mapper->getPrimaryTable()->getPrimaryKeyColumn();
    }

    /**
     * @return RelationObjectReference
     */
    abstract public function asObjectReference() : RelationObjectReference;

    /**
     * @param Select $select
     * @param string $relatedTableAlias
     *
     * @return void
     */
    public function addLoadToSelect(Select $select, string $relatedTableAlias)
    {
        $select->addColumn($this->primaryKeyColumn->getName(), Expr::column($relatedTableAlias, $this->primaryKeyColumn));
    }

    /**
     * @inheritDoc
     */
    public function getIdFromValue($childValue)
    {
        /** @var int|null $childValue */
        return $childValue;
    }

    /**
     * @param PersistenceContext $context
     * @param Column[]           $modifiedColumns
     * @param array              $children
     *
     * @return Row[]
     * @throws InvalidArgumentException
     */
    final protected function bulkUpdateForeignKeys(PersistenceContext $context, array $modifiedColumns, array $children) : array
    {
        $primaryKey     = $this->mapper->getPrimaryTable()->getPrimaryKeyColumn();
        $primaryKeyName = $primaryKey->getName();

        $columnsToPersist = array_merge([$primaryKey], $modifiedColumns);

        $rowSet = new RowSet($this->mapper->getPrimaryTable()->withColumnsButIgnoringConstraints($columnsToPersist));
        $rows   = [];

        foreach ($children as $key => $childId) {
            if ($childId !== null) {
                $row = $rowSet->createRow([$primaryKeyName => $childId]);
                $rowSet->add($row);
                $rows[$key] = $row;
            }
        }

        if ($modifiedColumns) {
            if ($rowSet->count() > 0) {
                $context->bulkUpdate($rowSet);
            }
        }

        return $rows;
    }
}