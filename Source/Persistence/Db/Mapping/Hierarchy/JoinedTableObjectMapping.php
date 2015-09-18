<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Query;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The joined table object mapping class.
 *
 * This uses class table inheritance storing the data in subclasses
 * in a separate tables. The primary key of the class table is
 * a foreign key of the parent table primary key.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class JoinedTableObjectMapping extends SubClassObjectMapping
{
    /**
     * @var Table
     */
    protected $classTable;

    /**
     * @var string
     */
    protected $classTablePrimaryKeyName;

    /**
     * @var string
     */
    protected $classTablePrefix;

    /**
     * @var string[]
     */
    protected $classTableColumnMap = [];


    /**
     * @param Table                     $parentTable
     * @param FinalizedMapperDefinition $definition
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Table $parentTable, FinalizedMapperDefinition $definition)
    {
        parent::__construct($parentTable, $definition, IRelation::DEPENDENT_CHILDREN, [$definition->getTable()]);
        $this->classTable               = $definition->getTable();
        $this->classTablePrimaryKeyName = $this->classTable->getPrimaryKeyColumnName();
        $this->classTablePrefix           = '__' . $this->classTable->getName() . '__';

        foreach ($this->getAllColumnsToLoad() as $columnName) {
            $this->classTableColumnMap[$columnName] = $this->classTablePrefix . $columnName;
        }
        $this->classTableColumnMap[$this->classTablePrimaryKeyName] = $this->classTablePrefix . $this->classTablePrimaryKeyName;
    }

    /**
     * {@inheritDoc}
     */
    public function withEmbeddedColumnsPrefixedBy($prefix)
    {
        $clone = parent::withEmbeddedColumnsPrefixedBy($prefix);

        // TODO: determine if this is necessary

        return $clone;
    }

    protected function processRowBeforeLoadSubclass(Row $row)
    {
        $columnData           = $row->getColumnData();
        $unprefixedColumnData = [];

        foreach ($this->classTableColumnMap as $column => $prefixedColumn) {
            $unprefixedColumnData[$column] = $columnData[$prefixedColumn];
        }

        return new Row($row->getTable(), $unprefixedColumnData + $columnData);
    }

    /**
     * {@inheritdoc}
     */
    public function rowMatchesObjectType(Row $row)
    {
        return $row->getColumn($this->classTablePrefix . $this->classTablePrimaryKeyName) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function makeClassConditionExpr(Query $query)
    {
        // TODO: verify alias safe
        return Expr::isNotNull(
                Expr::column($this->classTable->getName(), $this->classTable->getPrimaryKeyColumn())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addSpecificLoadToQuery(Query $query, $objectType)
    {
        // To only load these classes we can just perform an
        // inner join which will only load rows with the correct
        // foreign key.

        $parentAlias = $this->parentTable->getName();
        $joinAlias   = $query->getAliasFor($this->classTable->getName());

        $query->join(Join::inner(
                $this->classTable,
                $joinAlias,
                [$this->foreignKeyCondition($parentAlias, $joinAlias)]
        ));

        if ($query instanceof Select) {
            $this->addPrefixedColumnsToSelect($query, $joinAlias);
        }

        parent::addSpecificLoadToQuery($query, $objectType);
    }
    /**
     * {@inheritdoc}
     */
    protected function addLoadClausesToSelect(Select $select)
    {
        $parentAlias = $this->parentTable->getName();
        $joinAlias   = $select->getAliasFor($this->classTable->getName());

        $select->join(Join::left(
                $this->classTable,
                $joinAlias,
                [$this->foreignKeyCondition($parentAlias, $joinAlias)]
        ));

        $this->addPrefixedColumnsToSelect($select, $joinAlias);
    }

    /**
     * @param Select $select
     * @param        $joinAlias
     *
     * @return void
     */
    protected function addPrefixedColumnsToSelect(Select $select, $joinAlias)
    {
        foreach ($this->classTableColumnMap as $columnName => $prefixedColumnName) {
            $select->addColumn($prefixedColumnName, Expr::column($joinAlias, $this->classTable->getColumn($columnName)));
        }
    }

    /**
     * @param string $parentAlias
     * @param string $classTableAlias
     *
     * @return \Iddigital\Cms\Core\Persistence\Db\Query\Expression\BinOp
     */
    protected function foreignKeyCondition($parentAlias, $classTableAlias)
    {
        return Expr::equal(
                Expr::column($parentAlias, $this->parentTable->getPrimaryKeyColumn()),
                Expr::column($classTableAlias, $this->classTable->getPrimaryKeyColumn())
        );
    }


    /**
     * @inheritDoc
     */
    public function persistAll(
            PersistenceContext $context,
            array $objects,
            array $rows
    ) {
        // Map to separate rows in class table
        $classTableRows = [];
        foreach ($objects as $key => $object) {
            $classTableRows[$key] = new Row($this->classTable);
        }

        parent::persistAll($context, $objects, $classTableRows, $rows);
    }

    /**
     * {@inheritdoc}
     */
    protected function performPersist(PersistenceContext $context, array $classTableRows, array $parentRows = null)
    {
        /** @var Row[] $classTableRows */
        /** @var Row[] $parentRows */

        foreach ($parentRows as $key => $parentRow) {
            $classTableRow = $classTableRows[$key];
            if ($parentRow->hasPrimaryKey()) {
                $classTableRow->setPrimaryKey($parentRow->getPrimaryKey());
            } else {
                $parentRow->onInsertPrimaryKey(function ($id) use ($classTableRow) {
                    $classTableRow->setPrimaryKey($id);
                    $classTableRow->firePrimaryKeyCallbacks($id);
                });
            }
        }

        $context->upsert(new RowSet($this->classTable, $classTableRows));
    }

    /**
     * {@inheritdoc}
     */
    protected function performDelete(PersistenceContext $context, Delete $deleteQuery)
    {
        $subclassDelete = $deleteQuery->copy()->setTable($this->classTable);

        $fromAlias   = $subclassDelete->getTableAlias();
        $parentAlias = $subclassDelete->getAliasFor($deleteQuery->getTable()->getName());

        $subclassDelete->prependJoin(Join::inner(
                $deleteQuery->getTable(),
                $parentAlias,
                [$this->foreignKeyCondition($parentAlias, $fromAlias)]
        ));

        $context->queue($subclassDelete);
    }
}