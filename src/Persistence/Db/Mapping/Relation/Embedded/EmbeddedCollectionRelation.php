<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Embedded;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\ValueObjectCollection;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Dms\Core\Persistence\Db\Mapping\ParentMapBase;
use Dms\Core\Persistence\Db\Mapping\Relation\ISeparateToManyTableRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Mode\IdentifyingRelationMode;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The embedded collection relation class.
 *
 * This will map the children objects to a separate table that does
 * not contain a primary key but is related by a foreign key to the parent.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedCollectionRelation extends EmbeddedRelation implements ISeparateToManyTableRelation
{
    /**
     * @var IdentifyingRelationMode
     */
    private $mode;

    /**
     * @var string
     */
    private $parentTableName;

    /**
     * @var Column
     */
    private $parentPrimaryKey;

    /**
     * @var Column
     */
    private $primaryKey;

    /**
     * @var Column
     */
    private $foreignKeyToParent;

    /**
     * @var Table
     */
    private $childrenTable;

    /**
     * @param string                $idString
     * @param IEmbeddedObjectMapper $mapper
     * @param string                $tableName
     * @param string                $parentTableName
     * @param Column                $childPrimaryKey
     * @param Column                $foreignKeyToParentColumn
     * @param Column                $parentPrimaryKey
     */
    public function __construct(
            string $idString,
            IEmbeddedObjectMapper $mapper,
            string $parentTableName,
            string $tableName,
            Column $childPrimaryKey,
            Column $foreignKeyToParentColumn,
            Column $parentPrimaryKey
    ) {
        $this->parentTableName    = $parentTableName;
        $this->parentPrimaryKey   = $parentPrimaryKey;
        $this->primaryKey         = $childPrimaryKey;
        $this->foreignKeyToParent = $foreignKeyToParentColumn;
        $mapper                   = $this->loadMapperAsSeparateTable($mapper, $tableName);
        $this->childrenTable      = $mapper->getDefinition()->getTable();


        parent::__construct($idString, $mapper->getCollectionType(), $mapper, self::DEPENDENT_CHILDREN, [$this->childrenTable], [$parentPrimaryKey->getName()]);

        // Embedded values objects are always identifying
        // They must have be associated with a parent object
        $this->mode = new IdentifyingRelationMode();
    }

    /**
     * @inheritDoc
     */
    public function withReference(IToManyRelationReference $reference)
    {
        // TODO: determine if necessary
        throw NotImplementedException::method(__METHOD__);
    }

    private function loadMapperAsSeparateTable(IEmbeddedObjectMapper $mapper, $tableName)
    {
        return $mapper->asSeparateTable(
                $tableName,
                [$this->primaryKey, $this->foreignKeyToParent],
                [],
                [
                        ForeignKey::createWithNamingConvention(
                                $tableName,
                                [$this->foreignKeyToParent->getName()],
                                $this->parentTableName,
                                [$this->parentPrimaryKey->getName()],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        )
                ]
        );
    }

    /**
     * @param array $children
     *
     * @return ITypedCollection
     */
    public function buildCollection(array $children) : ITypedCollection
    {
        return $this->mapper->buildCollection($children);
    }

    /**
     * @param PersistenceContext $context
     * @param Delete             $parentDelete
     *
     * @return void
     */
    public function delete(PersistenceContext $context, Delete $parentDelete)
    {
        $this->mode->removeRelationsQuery(
                $context,
                $this->mapper,
                $parentDelete,
                $this->childrenTable,
                $this->foreignKeyToParent,
                $parentDelete->getTable()->getPrimaryKeyColumn()
        );
    }

    /**
     * @param PersistenceContext $context
     * @param ParentChildrenMap  $map
     *
     * @return void
     * @throws TypeMismatchException
     */
    public function persist(PersistenceContext $context, ParentChildrenMap $map)
    {
        if ($map->hasAnyParentsWithPrimaryKeys()) {
            $this->mode->syncInvalidatedRelationsQuery(
                    $context,
                    $this->childrenTable,
                    $this->foreignKeyToParent,
                    $this->getInvalidatedRelationExpr($map)
            );
        }

        $this->insertRelated($context, $map);
    }


    /**
     * @param PersistenceContext $context
     * @param ParentChildrenMap  $map
     *
     * @return void
     */
    protected function insertRelated(PersistenceContext $context, ParentChildrenMap $map)
    {
        $primaryKey = $map->getPrimaryKeyColumn();
        /** @var Row[] $parents */
        $parents = [];
        /** @var object[] $children */
        $children          = [];
        $childRows         = [];
        $childKeyParentMap = [];

        foreach ($map->getItems() as $key => $item) {
            $parents[$key] = $item->getParent();

            foreach ($item->getChildren() as $childKey => $child) {
                $uniqueKey                     = $key . '|' . $childKey;
                $children[$uniqueKey]          = $child;
                $childKeyParentMap[$uniqueKey] = $key;
                $childRows[$uniqueKey]         = new Row($this->childrenTable);
            }
        }

        $this->mapper->persistAllToRows($context, $children, $childRows);
        $rowGroups = [];

        foreach ($childKeyParentMap as $rowKey => $parentKey) {
            $rowGroups[$parentKey][] = $childRows[$rowKey];
        }

        foreach ($map->getItems() as $key => $item) {
            $parentRow = $item->getParent();
            $childRows = isset($rowGroups[$key]) ? $rowGroups[$key] : [];

            if ($parentRow->hasColumn($primaryKey)) {
                $this->setForeignKey($childRows, $this->foreignKeyToParent->getName(), $parentRow->getColumn($primaryKey));
            } else {
                $parentRow->onInsertPrimaryKey(function ($id) use ($childRows) {
                    $this->setForeignKey($childRows, $this->foreignKeyToParent->getName(), $id);
                });
            }
        }
    }

    protected function getInvalidatedRelationExpr(ParentChildrenMap $map)
    {
        // foreign_key_to_parent IN (<parent keys>)
        $parentIds = $map->getAllParentPrimaryKeys();

        return $parentIds
                ? Expr::in(
                        Expr::tableColumn($this->childrenTable, $this->foreignKeyToParent->getName()),
                        Expr::idParamTuple($parentIds)
                )
                : Expr::false();
    }

    /**
     * @inheritDoc
     */
    public function getRelationSelectFromParentRows(ParentMapBase $map, &$parentIdColumnName = null, &$mapIdColumn = null) : \Dms\Core\Persistence\Db\Query\Select
    {
        $parentIds = $map->getAllParentPrimaryKeys();

        $foreignKeyName = $this->foreignKeyToParent->getName();
        $select         = Select::from($this->childrenTable);
        $select->addRawColumn($foreignKeyName);
        $select->where(Expr::in(
                Expr::column(
                        $this->childrenTable->getName(),
                        $this->foreignKeyToParent
                ),
                Expr::idParamTuple($parentIds)
        ));

        $parentIdColumnName = $foreignKeyName;

        return $select;
    }

    /**
     * @param LoadingContext    $context
     * @param ParentChildrenMap $map
     *
     * @return mixed
     */
    public function load(LoadingContext $context, ParentChildrenMap $map)
    {
        if (!$map->getItems()) {
            return;
        }
        
        $select = $this->getRelationSelectFromParentRows($map, $parentIdColumnName);

        $this->loadFromSelect($context, $map, $select, $select->getTableAlias(), $parentIdColumnName);
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

        $this->mapper->getMapping()->addLoadToSelect($select, $relatedTableAlias);

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

        $allChildren = $this->mapper->loadAll($context, $flattenedResults);

        foreach ($map->getItems() as $item) {
            $parentKey = $item->getParent()->getColumn($primaryKey);
            $children  = [];

            if (isset($indexedGroups[$parentKey])) {
                foreach ($indexedGroups[$parentKey] as $key => $row) {
                    $children[] = $allChildren[$parentKey . '|' . $key];
                }
            }

            $item->setChildren($children);
        }
    }


    /**
     * @inheritDoc
     */
    public function joinSelectToRelatedTable(string $parentTableAlias, string $joinType, Select $select) : string
    {
        $childTableAlias = $select->generateUniqueAliasFor($this->childrenTable->getName());

        $select->join(new Join($joinType, $this->childrenTable, $childTableAlias, [
                $this->getRelationJoinCondition($parentTableAlias, $childTableAlias)
        ]));

        return $childTableAlias;
    }

    /**
     * @inheritDoc
     */
    public function getRelationSubSelect(Select $outerSelect, string $parentTableAlias) : \Dms\Core\Persistence\Db\Query\Select
    {
        $subSelect = $outerSelect->buildSubSelect($this->childrenTable);

        return $subSelect
                ->where($this->getRelationJoinCondition($parentTableAlias, $subSelect->getTableAlias()));
    }

    /**
     * @inheritDoc
     */
    public function getRelationJoinCondition(string $parentTableAlias, string $relatedTableAlias) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        return Expr::equal(
                Expr::column($parentTableAlias, $this->parentPrimaryKey),
                Expr::column($relatedTableAlias, $this->foreignKeyToParent)
        );
    }
}