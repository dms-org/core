<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Dms\Core\Persistence\Db\Mapping\ParentMapBase;
use Dms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Clause\Ordering;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;

/**
 * The to many relation class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelation extends ToManyRelationBase
{
    /**
     * @var string
     */
    protected $foreignKeyToParent;

    /**
     * @var Column
     */
    protected $foreignKeyToParentColumn;

    /**
     * The order to load the related objects.
     * The column names as keys and the direction as values.
     *
     * @var string[]
     */
    private $orderByColumnNameDirectionMap;

    /**
     * The column to save the order index (1 based)
     * of the entity in it's parents collection.
     *
     * @var Column|null
     */
    protected $orderPersistColumn;

    /**
     * @param string                   $idString
     * @param IToManyRelationReference $reference
     * @param string                   $parentForeignKey
     * @param IRelationMode            $mode
     * @param string[]                 $orderByColumnNameDirectionMap
     * @param string|null              $orderPersistColumn
     *
     * @throws InvalidArgumentException
     * @throws InvalidRelationException
     */
    public function __construct(
        string $idString,
        IToManyRelationReference $reference,
        string $parentForeignKey,
        IRelationMode $mode,
        array $orderByColumnNameDirectionMap = [],
        string $orderPersistColumn = null
    ) {
        parent::__construct($idString, $reference, $mode, self::DEPENDENT_CHILDREN);
        $this->foreignKeyToParent       = $parentForeignKey;
        $this->foreignKeyToParentColumn = $this->relatedTable->getColumn($this->foreignKeyToParent);

        $this->orderByColumnNameDirectionMap = $orderByColumnNameDirectionMap;

        foreach ($orderByColumnNameDirectionMap as $columnName => $direction) {
            $this->relatedTable->getColumn($columnName);
        }

        if ($orderPersistColumn) {
            $this->orderPersistColumn = $this->relatedTable->findColumn($orderPersistColumn);

            if (!$this->orderPersistColumn) {
                throw InvalidRelationException::format(
                    'Invalid order persist column %s does not exist on related table %s',
                    $orderPersistColumn, $this->relatedTable->getName()
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function withReference(IToManyRelationReference $reference)
    {
        return new self(
            $this->idString,
            $reference,
            $this->foreignKeyToParent,
            $this->mode,
            $this->orderByColumnNameDirectionMap,
            $this->orderPersistColumn ? $this->orderPersistColumn->getName() : null
        );
    }

    public function persist(PersistenceContext $context, ParentChildrenMap $map)
    {
        if ($map->hasAnyParentsWithPrimaryKeys() && !$context->getConnection()->getPlatform()->supportsForeignKeys()) {
            $this->mode->syncInvalidatedRelationsQuery(
                $context,
                $this->relatedTable,
                $this->foreignKeyToParentColumn,
                $this->getInvalidatedRelationExpr($map)
            );
        }

        $this->insertRelated($context, $map);
    }

    protected function deleteByParentQuery(PersistenceContext $context, Delete $parentDelete)
    {
        $isSelfReferencing = $parentDelete->getTable()->getName() === $this->mapper->getPrimaryTableName();
        $foreignKey        = $this->relatedTable->getForeignKeyFor(
            $this->foreignKeyToParent,
            $parentDelete->getTable()->getName(),
            $parentDelete->getTable()->getPrimaryKeyColumnName()
        );

        if ($isSelfReferencing && $foreignKey->getOnDeleteMode() === ForeignKeyMode::CASCADE) {
            return;
        }

        $this->mode->removeRelationsQuery(
            $context,
            $this->mapper,
            $parentDelete,
            $this->relatedTable,
            $this->foreignKeyToParentColumn,
            $parentDelete->getTable()->getPrimaryKeyColumn()
        );
    }

    /**
     * @param PersistenceContext $context
     * @param ParentChildrenMap  $map
     *
     * @return void
     */
    protected function insertRelated(PersistenceContext $context, ParentChildrenMap $map)
    {
        $primaryKey         = $map->getPrimaryKeyColumn();
        $orderPersistColumn = $this->orderPersistColumn ? $this->orderPersistColumn->getName() : null;
        /** @var Row[] $parents */
        $parents = [];
        /** @var IEntity[] $children */
        $children          = [];
        $childKeyParentMap = [];

        foreach ($map->getItems() as $key => $item) {
            $parents[$key] = $item->getParent();

            foreach ($item->getChildren() as $childKey => $child) {
                $uniqueKey                     = $key . '|' . $childKey;
                $children[$uniqueKey]          = $child;
                $childKeyParentMap[$uniqueKey] = $key;
            }
        }

        /** @var Row[][] $rowGroups */
        $rowGroups = [];
        $rows      = $this->reference->syncRelated(
            $context,
            array_filter([$this->foreignKeyToParentColumn, $this->orderPersistColumn]),
            $children
        );

        foreach ($childKeyParentMap as $rowKey => $parentKey) {
            $rowGroups[$parentKey][] = $rows[$rowKey];
        }

        $selfReferencingChildRows = [];

        foreach ($map->getItems() as $key => $item) {
            $parentRow = $item->getParent();
            $childRows = isset($rowGroups[$key]) ? $rowGroups[$key] : [];

            if ($orderPersistColumn) {
                $order = 1;
                foreach ($childRows as $row) {
                    $row->setColumn($orderPersistColumn, $order);
                    $order++;
                }
            }

            if ($parentRow->hasColumn($primaryKey)) {
                $this->setForeignKey($childRows, $this->foreignKeyToParent, $parentRow->getColumn($primaryKey));
            } else {
                $parentRow->onInsertPrimaryKey(function ($id) use ($childRows) {
                    $this->setForeignKey($childRows, $this->foreignKeyToParent, $id);
                });

                foreach ($childRows as $row) {
                    if ($parentRow === $row) {
                        $selfReferencingChildRows[] = $row;
                    }
                }
            }
        }

        if ($selfReferencingChildRows) {
            // If the rows are self-referencing and need to be inserted
            // an extra step must be taken because the primary key will
            // only be known after inserting so the foreign key to itself
            // will have to be updated separately afterwards
            $context->bulkUpdate(new RowSet($this->relatedTable->withColumnsButIgnoringConstraints([
                $this->relatedPrimaryKey,
                $this->foreignKeyToParentColumn,
            ]), $selfReferencingChildRows));
        }
    }

    protected function getInvalidatedRelationExpr(ParentChildrenMap $map)
    {
        // For parent each row:
        // foreign_key_to_parent = <parent key> AND primary_key NOT IN (<current children keys>)
        $primaryKey  = $map->getPrimaryKeyColumn();
        $expressions = [];

        foreach ($map->getItems() as $item) {
            $parent = $item->getParent();
            if ($parent->hasColumn($primaryKey)) {

                $equalsParentForeignKey = Expr::equal(
                    $this->column($this->foreignKeyToParentColumn),
                    Expr::idParam($parent->getColumn($primaryKey))
                );

                $childrenIds = [];
                foreach ($item->getChildren() as $child) {
                    $childId = $this->reference->getIdFromValue($child);
                    if ($childId) {
                        $childrenIds[] = Expr::idParam($childId);
                    }
                }

                if ($childrenIds) {
                    $expressions[] = Expr::and_(
                        $equalsParentForeignKey,
                        Expr::notIn($this->column($this->relatedTable->getPrimaryKeyColumn()), Expr::tuple($childrenIds))
                    );
                } else {
                    $expressions[] = $equalsParentForeignKey;
                }
            }
        }

        return $expressions ? Expr::compoundOr($expressions) : Expr::false();
    }

    /**
     * @inheritDoc
     */
    public function getRelationSelectFromParentRows(ParentMapBase $map, &$parentIdColumnName = null, &$mapIdColumn = null) : Select
    {
        $parentIds = $map->getAllParentPrimaryKeys();

        $select = $this->select()
            ->addRawColumn($this->foreignKeyToParent)
            ->where(Expr::in($this->column($this->foreignKeyToParentColumn), Expr::idParamTuple($parentIds)));

        $this->addOrderByClausesToSelect($select, $this->relatedTable->getName());

        $parentIdColumnName = $this->foreignKeyToParent;

        return $select;
    }

    /**
     * @inheritDoc
     */
    public function loadFromSelect(
        LoadingContext $context,
        ParentChildrenMap $map,
        Select $select,
        string $relatedTableAlias,
        string $parentIdColumnName
    ) {
        $primaryKey = $map->getPrimaryKeyColumn();

        $this->reference->addLoadToSelect($select, $relatedTableAlias);

        $indexedGroups = [];

        $rows = $context->query($select)->getRows();
        foreach ($rows as $row) {
            $indexedGroups[$row->getColumn($parentIdColumnName)][] = $row;
        }

        $flattenedResults = [];

        foreach ($indexedGroups as $parentKey => $group) {
            foreach ($group as $key => $row) {
                $flattenedResults[$parentKey . '|' . $key] = $row;
            }
        }

        $values = $this->reference->loadCollectionValues($context, $flattenedResults);

        foreach ($map->getItems() as $item) {
            $parentKey   = $item->getParent()->getColumn($primaryKey);
            $childValues = [];

            if (isset($indexedGroups[$parentKey])) {
                foreach ($indexedGroups[$parentKey] as $key => $row) {
                    $childValues[] = $values[$parentKey . '|' . $key];
                }
            }

            $item->setChildren($childValues);
        }
    }

    private function addOrderByClausesToSelect(Select $select, $tableAlias)
    {
        foreach ($this->orderByColumnNameDirectionMap as $column => $direction) {
            $select->orderBy(new Ordering(Expr::column($tableAlias, $this->relatedTable->getColumn($column)), $direction));
        }
    }

    /**
     * @inheritDoc
     */
    public function joinSelectToRelatedTable(string $parentTableAlias, string $joinType, Select $select) : string
    {
        $relatedTableAlias = $select->generateUniqueAliasFor($this->relatedTable->getName());

        $select->join(new Join($joinType, $this->relatedTable, $relatedTableAlias, [
            $this->getRelationJoinCondition($parentTableAlias, $relatedTableAlias),
        ]));

        $this->addOrderByClausesToSelect($select, $relatedTableAlias);

        return $relatedTableAlias;
    }

    /**
     * @inheritDoc
     */
    public function getRelationSubSelect(Select $outerSelect, string $parentTableAlias) : Select
    {
        $subSelect = $outerSelect->buildSubSelect($this->relatedTable);

        $this->mapper->loadSelect($subSelect);
        $subSelect->where($this->getRelationJoinCondition($parentTableAlias, $subSelect->getTableAlias()));

        $this->addOrderByClausesToSelect($subSelect, $subSelect->getTableAlias());

        return $subSelect;
    }

    /**
     * @inheritDoc
     */
    public function getRelationJoinCondition(string $parentTableAlias, string $relatedTableAlias) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        return Expr::equal(
            Expr::column($parentTableAlias, $this->relatedPrimaryKey),
            Expr::column($relatedTableAlias, $this->foreignKeyToParentColumn)
        );
    }
}