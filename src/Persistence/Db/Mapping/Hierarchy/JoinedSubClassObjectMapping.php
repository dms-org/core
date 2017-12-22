<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hierarchy;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Query;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The joined table object mapping class.
 *
 * This uses class table inheritance storing the data in subclasses
 * in a separate tables. The primary key of the class table is
 * a foreign key of the parent table primary key.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class JoinedSubClassObjectMapping extends SubClassObjectMapping
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
        parent::__construct($parentTable, $definition, IRelation::DEPENDENT_CHILDREN);

        $subClassTable = $definition->getTable();
        $definition->addForeignKey(ForeignKey::createWithNamingConvention(
                $subClassTable->getName(),
                [$subClassTable->getPrimaryKeyColumnName()],
                $parentTable->getName(),
                [$parentTable->getPrimaryKeyColumnName()],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::CASCADE
        ));
    }

    protected function loadFromDefinition(FinalizedMapperDefinition $definition)
    {
        parent::loadFromDefinition($definition);
        $this->classTable               = $definition->getTable();
        $this->classTablePrimaryKeyName = $this->classTable->getPrimaryKeyColumnName();
        $this->classTablePrefix         = '__' . $this->classTable->getName() . '__';

        foreach ($this->getAllColumnsToLoad() as $columnName) {
            $this->classTableColumnMap[$columnName] = $this->classTablePrefix . $columnName;
        }
        $this->classTableColumnMap[$this->classTablePrimaryKeyName] = $this->classTablePrefix . $this->classTablePrimaryKeyName;
    }

    protected function loadMappingTables(FinalizedMapperDefinition $definition)
    {
        return [$definition->getTable()];
    }

    /**
     * {@inheritDoc}
     */
    public function withEmbeddedColumnsPrefixedBy(string $prefix)
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
    public function rowMatchesObjectType(Row $row) : bool
    {
        return $row->getColumn($this->classTablePrefix . $this->classTablePrimaryKeyName) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function makeClassConditionExpr(Query $query) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        // TODO: verify alias safe
        return Expr::isNotNull(
                Expr::column($this->classTable->getName(), $this->classTable->getPrimaryKeyColumn())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addSpecificLoadToQuery(Query $query, string $objectType, array &$subclassTableAliases = [])
    {
        // To only load these classes we can just perform an
        // inner join which will only load rows with the correct
        // foreign key.

        $parentAlias = $this->parentTable->getName();
        $joinAlias   = $query->generateUniqueAliasFor($this->classTable->getName());

        $subclassTableAliases[$this->getObjectType()] = $joinAlias;

        $query->join(Join::inner(
                $this->classTable,
                $joinAlias,
                [$this->foreignKeyCondition($parentAlias, $joinAlias)]
        ));

        if ($query instanceof Select) {
            $this->addPrefixedColumnsToSelect($query, $joinAlias);
        }

        parent::addSpecificLoadToQuery($query, $objectType, $subclassTableAliases);
    }

    /**
     * {@inheritdoc}
     */
    protected function addLoadClausesToSelect(Select $select, string $parentAlias, array &$subclassTableAliases = []) : string
    {
        $joinAlias = $select->generateUniqueAliasFor($this->classTable->getName());

        $subclassTableAliases[$this->getObjectType()] = $joinAlias;

        $select->join(Join::left(
                $this->classTable,
                $joinAlias,
                [$this->foreignKeyCondition($parentAlias, $joinAlias)]
        ));

        $this->addPrefixedColumnsToSelect($select, $joinAlias);

        return $joinAlias;
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
            $select->addColumn($prefixedColumnName, Expr::column($joinAlias, $this->classTable->findColumn($columnName)));
        }
    }

    /**
     * @param string $parentAlias
     * @param string $classTableAlias
     *
     * @return \Dms\Core\Persistence\Db\Query\Expression\BinOp
     */
    protected function foreignKeyCondition(string $parentAlias, string $classTableAlias)
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
            array $rows,
            array $extraData = null
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
        $parentAlias = $subclassDelete->generateUniqueAliasFor($deleteQuery->getTable()->getName());

        $subclassDelete->prependJoin(Join::inner(
                $deleteQuery->getTable(),
                $parentAlias,
                [$this->foreignKeyCondition($parentAlias, $fromAlias)]
        ));

        $context->queue($subclassDelete);
    }
}