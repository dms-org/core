<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Dms\Core\Persistence\Db\Mapping\ParentMapBase;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Pinq\Iterators\Common\Identity;

/**
 * The many to many relation class.
 *
 * NOTE: Because bidirectional inverse relations are ignored
 * when persisting (to avoid infinite recursion) the inverse
 * many-to-many relation will not be synced. Hence the entities
 * are assumed to maintain a consistent bidirectional state
 * where all the related entities are in sync on both sides.
 * This is then consistent with how the relational data will
 * be stored.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ManyToManyRelation extends ToManyRelationBase
{
    /**
     * @var Table
     */
    private $joinTable;

    /**
     * @var string
     */
    private $parentTableName;

    /**
     * @var Column
     */
    private $parentTablePrimaryKey;

    /**
     * @var Column
     */
    private $parentForeignKeyColumn;

    /**
     * @var array
     */
    private $relatedTableName;

    /**
     * @var Column
     */
    private $relatedTablePrimaryKey;

    /**
     * @var Column
     */
    private $relatedForeignKeyColumn;

    /**
     * ManyToManyRelation constructor.
     *
     * @param string                   $idString
     * @param IToManyRelationReference $reference
     * @param string                   $joinTableName
     * @param string                   $parentTableName
     * @param Column                   $parentTablePrimaryKey
     * @param string                   $parentForeignKeyColumnName
     * @param string                   $relatedForeignKeyColumnName
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
            string $idString,
            IToManyRelationReference $reference,
            string $joinTableName,
            string $parentTableName,
            Column $parentTablePrimaryKey,
            string $parentForeignKeyColumnName,
            string $relatedForeignKeyColumnName
    ) {
        $mapper                       = $reference->getMapper();
        $orm                          = $mapper->getDefinition()->getOrm();
        $this->parentTableName        = $parentTableName;
        $this->parentTablePrimaryKey  = $parentTablePrimaryKey;
        $this->parentForeignKeyColumn = new Column($parentForeignKeyColumnName, Integer::normal());

        $this->relatedTableName        = $mapper->getPrimaryTableName();
        $this->relatedTablePrimaryKey  = $mapper->getPrimaryTable()->getPrimaryKeyColumn();
        $this->relatedForeignKeyColumn = new Column($relatedForeignKeyColumnName, Integer::normal());

        $inverseRelation = $reference->getBidirectionalRelation();
        if ($inverseRelation) {
            if (!($inverseRelation instanceof self)) {
                throw InvalidArgumentException::format(
                        'Invalid bidirectional relation for many-to-many relation: expecting instance of %s, %s given',
                        __CLASS__, get_class($inverseRelation)
                );
            }

            $joinTable = $inverseRelation->joinTable;
        } else {
            $joinTable = $this->buildJoinTable($joinTableName)->withNameAndConstraintsPrefixedBy($orm->getNamespace());
        }

        parent::__construct($idString, $reference, null, self::DEPENDENT_CHILDREN, [
                $joinTable
        ]);

        $this->joinTable = $joinTable;
    }

    /**
     * @inheritDoc
     */
    public function withReference(IToManyRelationReference $reference)
    {
        return new self(
                $this->idString,
                $reference,
                $this->joinTable->getName(),
                $this->parentTableName,
                $this->parentTablePrimaryKey,
                $this->parentForeignKeyColumn->getName(),
                $this->relatedForeignKeyColumn->getName()
        );
    }

    private function buildJoinTable($name)
    {
        return new Table($name, [$this->parentForeignKeyColumn, $this->relatedForeignKeyColumn], [], [
                ForeignKey::createWithNamingConvention(
                        $name,
                        [$this->parentForeignKeyColumn->getName()],
                        $this->parentTableName,
                        [$this->parentTablePrimaryKey->getName()],
                        ForeignKeyMode::CASCADE, ForeignKeyMode::CASCADE
                ),
                ForeignKey::createWithNamingConvention(
                        $name,
                        [$this->relatedForeignKeyColumn->getName()],
                        $this->relatedTableName,
                        [$this->relatedTablePrimaryKey->getName()],
                        ForeignKeyMode::CASCADE, ForeignKeyMode::CASCADE
                ),
        ]);
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
        $primaryKey        = $map->getPrimaryKeyColumn();
        $relatedPrimaryKey = $this->mapper->getPrimaryTable()->getPrimaryKeyColumnName();
        $children          = $map->getAllChildren();
        $rows              = $this->reference->syncRelated($context, [], $children);

        /** @var Row[] $childRowMap */
        $childRowMap = [];
        foreach ($children as $key => $child) {
            $childRowMap[Identity::hash($child)] = $rows[$key];
        }

        // Clear join rows and reinsert to sync
        if ($map->hasAnyParentsWithPrimaryKeys()) {
            $delete = Delete::from($this->joinTable)
                    ->where($this->getInvalidatedRelationExpr($map));
            $context->queue($delete);
        }

        $parentIdColumn  = $this->parentForeignKeyColumn->getName();
        $relatedIdColumn = $this->relatedForeignKeyColumn->getName();
        $joinTableRows   = [];

        foreach ($map->getItems() as $item) {
            $parent = $item->getParent();

            foreach ($item->getChildren() as $child) {
                $joinRow = new Row($this->joinTable);

                if ($parent->hasColumn($primaryKey)) {
                    $joinRow->setColumn($parentIdColumn, $parent->getColumn($primaryKey));
                } else {
                    $parent->onInsertPrimaryKey(function ($id) use ($joinRow, $parentIdColumn) {
                        $joinRow->setColumn($parentIdColumn, $id);
                    });
                }

                $childRow = $childRowMap[Identity::hash($child)];
                if ($childRow->hasColumn($relatedPrimaryKey)) {
                    $joinRow->setColumn($relatedIdColumn, $childRow->getColumn($relatedPrimaryKey));
                } else {
                    $childRow->onInsertPrimaryKey(function ($id) use ($joinRow, $relatedIdColumn) {
                        $joinRow->setColumn($relatedIdColumn, $id);
                    });
                }

                $joinTableRows[] = $joinRow;
            }
        }

        if (!empty($joinTableRows)) {
            $context->upsert(new RowSet($this->joinTable, $joinTableRows));
        }
    }


    protected function getInvalidatedRelationExpr(ParentChildrenMap $map)
    {
        // For each parent row:
        // foreign_key_to_parent = <parent key>
        $primaryKey  = $map->getPrimaryKeyColumn();
        $expressions = [];

        foreach ($map->getItems() as $item) {
            $parent = $item->getParent();
            if ($parent->hasColumn($primaryKey)) {
                $expressions[] = Expr::equal(
                        Expr::tableColumn($this->joinTable, $this->parentForeignKeyColumn->getName()),
                        Expr::idParam($parent->getColumn($primaryKey))
                );
            }
        }

        return $expressions ? Expr::compoundOr($expressions) : Expr::false();
    }

    /**
     * @inheritDoc
     */
    public function getRelationSelectFromParentRows(ParentMapBase $map, &$parentIdColumnName = null) : \Dms\Core\Persistence\Db\Query\Select
    {
        // SELECT <related>.*, <parent id> FROM <related>
        // INNER JOIN <join table> ON <join table>.<related key> = <related>.<primary key>
        // WHERE <join table>.<parent key> IN <parent ids>
        $primaryKey = $map->getPrimaryKeyColumn();
        $select     = $this->select();
        $parentIds  = [];

        foreach ($map->getAllParents() as $parent) {
            $parentIds[] = Expr::idParam($parent->getColumn($primaryKey));
        }

        $alias = $select->generateUniqueAliasFor($this->joinTable->getName());
        $select->join(Join::inner($this->joinTable, $alias, [
                Expr::equal(Expr::column($alias, $this->relatedForeignKeyColumn), $this->column($this->relatedPrimaryKey))
        ]));

        $parentIdColumn = Expr::column($alias, $this->parentForeignKeyColumn);
        $select->where(Expr::in($parentIdColumn, Expr::tuple($parentIds)));
        $select->addColumn($this->parentForeignKeyColumn->getName(), $parentIdColumn);

        $parentIdColumnName = $parentIdColumn->getName();

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

    protected function deleteByParentQuery(PersistenceContext $context, Delete $parentDelete)
    {
        // DELETE <join table>
        // INNER JOIN <parent table> ON  <parent table>.<primary key> = <join table>.<parent key>
        // WHERE <delete parent conditions>

        $delete           = $parentDelete->copy()->setTable($this->joinTable);
        $alias            = $delete->generateUniqueAliasFor($parentDelete->getTable()->getName());
        $parentPrimaryKey = Expr::column($alias, $parentDelete->getTable()->getPrimaryKeyColumn());

        $joinOnCondition = Expr::equal(
                $parentPrimaryKey,
                Expr::tableColumn($this->joinTable, $this->parentForeignKeyColumn->getName())
        );

        $isSelfReferencing = $parentDelete->getTable()->getName() === $this->mapper->getPrimaryTableName();
        if ($isSelfReferencing) {
            // If the relation is self-referencing the join condition becomes
            // INNER JOIN <parent table> ON  <parent table>.<primary key> = <join table>.<parent key> OR <parent table>.<primary key> = <join table>.<related key>
            $joinOnCondition = Expr::or_($joinOnCondition, Expr::equal(
                    $parentPrimaryKey,
                    Expr::tableColumn($this->joinTable, $this->relatedForeignKeyColumn->getName())
            ));
        }

        $relatedDelete = $delete
                ->join(Join::inner($parentDelete->getTable(), $alias, [$joinOnCondition]));

        $context->queue($relatedDelete);
    }

    /**
     * @inheritDoc
     */
    public function joinSelectToRelatedTable(string $parentTableAlias, string $joinType, Select $select) : string
    {
        $joinTableAlias = $select->generateUniqueAliasFor($this->joinTable->getName());

        $select->join(new Join($joinType, $this->joinTable, $joinTableAlias, [
                $this->getRelationJoinCondition($parentTableAlias, $joinTableAlias)
        ]));

        $relatedTableAlias = $select->generateUniqueAliasFor($this->relatedTable->getName());

        $select->join(new Join(Join::INNER, $this->relatedTable, $relatedTableAlias, [
                Expr::equal(
                        Expr::column($joinTableAlias, $this->relatedForeignKeyColumn),
                        Expr::column($relatedTableAlias, $this->relatedPrimaryKey)
                )
        ]));

        return $relatedTableAlias;
    }

    /**
     * @inheritDoc
     */
    public function getRelationSubSelect(Select $outerSelect, string $parentTableAlias) : \Dms\Core\Persistence\Db\Query\Select
    {
        $subSelect = $outerSelect->buildSubSelect($this->relatedTable);

        $joinTableAlias = $subSelect->generateUniqueAliasFor($this->joinTable->getName());

        $subSelect->join(Join::inner($this->joinTable, $joinTableAlias, [
                Expr::equal(
                        Expr::column($joinTableAlias, $this->relatedForeignKeyColumn),
                        Expr::column($subSelect->getTableAlias(), $this->relatedPrimaryKey)
                )
        ]));


        return $subSelect
                ->where($this->getRelationJoinCondition($parentTableAlias, $joinTableAlias));
    }

    /**
     * @inheritDoc
     */
    public function getRelationJoinCondition(string $parentTableAlias, string $relatedTableAlias) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        $joinTableAlias = $relatedTableAlias;

        return Expr::equal(
                Expr::column($parentTableAlias, $this->parentTablePrimaryKey),
                Expr::column($joinTableAlias, $this->parentForeignKeyColumn)
        );
    }
}