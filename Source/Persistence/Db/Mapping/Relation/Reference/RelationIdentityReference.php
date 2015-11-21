<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

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
     */
    public function __construct(IEntityMapper $mapper)
    {
        parent::__construct($mapper);
        $this->primaryKeyColumn = $this->mapper->getPrimaryTable()->getPrimaryKeyColumn();
    }

    /**
     * @return RelationObjectReference
     */
    abstract public function asObjectReference();

    /**
     * @return Select
     */
    public function getSelect()
    {
        return Select::from($this->mapper->getPrimaryTable())
                ->addRawColumn($this->primaryKeyColumn->getName());
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
    final protected function bulkUpdateForeignKeys(PersistenceContext $context, array $modifiedColumns, array $children)
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