<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IRelationReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\ColumnExpr;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EntityRelation extends Relation implements IRelation
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
     * @var Table
     */
    protected $table;

    /**
     * @var Column
     */
    protected $primaryKey;

    /**
     * Relation constructor.
     *
     * @param IRelationReference $reference
     * @param IRelationMode|null $mode
     * @param string             $dependencyMode
     * @param Table[]            $relationshipTables
     * @param string[]           $parentColumnsToLoad
     */
    public function __construct(
            IRelationReference $reference,
            IRelationMode $mode = null,
            $dependencyMode,
            array $relationshipTables = [],
            array $parentColumnsToLoad = []
    ) {
        parent::__construct($reference->getMapper(), $dependencyMode, $relationshipTables, $parentColumnsToLoad);

        $this->reference  = $reference;
        $this->mapper     = $reference->getMapper();
        $this->mode       = $mode;
        $this->table      = $this->mapper->getPrimaryTable();
        $this->primaryKey = $this->table->getPrimaryKeyColumn();
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
    final protected function rowSet(array $rows = [])
    {
        return new RowSet(
                $this->table,
                $rows
        );
    }

    /**
     * @return Select
     */
    final protected function select()
    {
        return $this->reference->getSelect();
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
    protected function column(Column $column)
    {
        return Expr::column($this->table->getName(), $column);
    }
}