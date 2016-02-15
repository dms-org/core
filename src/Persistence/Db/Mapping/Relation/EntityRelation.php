<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IRelationReference;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\RelationIdentityReference;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\ColumnExpr;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EntityRelation extends Relation implements ISeparateTableRelation
{
    /**
     * @var IRelationReference
     */
    protected $reference;

    /**
     * @var IEntityMapper
     */
    protected $mapper;

    /**
     * @var IRelationMode|null
     */
    protected $mode;

    /**
     * The related table.
     *
     * @var Table
     */
    protected $relatedTable;

    /**
     * The primary key of the related table.
     *
     * @var Column
     */
    protected $relatedPrimaryKey;

    /**
     * EntityRelation constructor.
     *
     * @param string             $idString
     * @param IRelationReference $reference
     * @param IRelationMode|null $mode
     * @param string             $dependencyMode
     * @param Table[]            $relationshipTables
     * @param string[]           $parentColumnsToLoad
     */
    public function __construct(
            string $idString,
            IRelationReference $reference,
            IRelationMode $mode = null,
            string $dependencyMode,
            array $relationshipTables = [],
            array $parentColumnsToLoad = []
    ) {
        parent::__construct($idString, $reference->getMapper(), $dependencyMode, $relationshipTables, $parentColumnsToLoad);

        $this->reference = $reference;
        $this->mapper    = $reference->getMapper();
        $this->mode      = $mode;

        $this->relatedTable      = $this->mapper->getPrimaryTable();
        $this->relatedPrimaryKey = $this->relatedTable->getPrimaryKeyColumn();

        $this->mapper->onUpdatedPrimaryTable(function (Table $primaryTable) {
            $this->relatedTable      = $primaryTable;
            $this->relatedPrimaryKey = $primaryTable->getPrimaryKeyColumn();
        });
    }

    /**
     * @return static
     */
    final public function withObjectReference()
    {
        $clone = clone $this;

        if ($clone->reference instanceof RelationIdentityReference) {
            $clone->reference = $clone->reference->asObjectReference();
        }

        return $clone;
    }

    /**
     * @return IRelationReference
     */
    final public function getReference() : Reference\IRelationReference
    {
        return $this->reference;
    }

    /**
     * @return IEntityMapper
     */
    final public function getEntityMapper() : \Dms\Core\Persistence\Db\Mapping\IEntityMapper
    {
        return $this->mapper;
    }

    /**
     * @return Column
     */
    final public function getRelatedPrimaryKey() : \Dms\Core\Persistence\Db\Schema\Column
    {
        return $this->mapper->getPrimaryTable()->getPrimaryKeyColumn();
    }

    /**
     * @return IRelationMode|null
     */
    final public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param array $rows
     *
     * @return RowSet
     */
    final protected function rowSet(array $rows = []) : \Dms\Core\Persistence\Db\RowSet
    {
        return new RowSet(
                $this->relatedTable,
                $rows
        );
    }

    /**
     * @return Select
     */
    final protected function select() : \Dms\Core\Persistence\Db\Query\Select
    {
        return Select::from($this->relatedTable);
    }

    /**
     * @inheritDoc
     */
    final public function delete(PersistenceContext $context, Delete $parentDelete)
    {
        $this->deleteByParentQuery($context, $parentDelete);
    }

    abstract protected function deleteByParentQuery(PersistenceContext $context, Delete $parentDelete);

    /**
     * @param Column $column
     *
     * @return ColumnExpr
     */
    protected function column(Column $column) : \Dms\Core\Persistence\Db\Query\Expression\ColumnExpr
    {
        return Expr::column($this->relatedTable->getName(), $column);
    }
}