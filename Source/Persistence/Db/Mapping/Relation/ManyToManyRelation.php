<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Pinq\Iterators\Common\Identity;

/**
 * The many to many relation class.
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
     * @var Column
     */
    private $parentIdColumn;

    /**
     * @var Column
     */
    private $relatedIdColumn;

    /**
     * ManyToManyRelation constructor.
     *
     * @param IToManyRelationReference $reference
     * @param string                   $joinTableName
     * @param string                   $parentIdColumn
     * @param string                   $relatedIdColumn
     */
    public function __construct(
            IToManyRelationReference $reference,
            $joinTableName,
            $parentIdColumn,
            $relatedIdColumn
    ) {
        $this->parentIdColumn  = new Column($parentIdColumn, Integer::normal());
        $this->relatedIdColumn = new Column($relatedIdColumn, Integer::normal());
        $joinTable             = $this->buildJoinTable($joinTableName);

        parent::__construct($reference, null, self::DEPENDENT_CHILDREN, [
                $joinTable
        ]);

        $this->joinTable = $joinTable;
    }

    /**
     * @inheritDoc
     */
    public function withReference(IToManyRelationReference $reference)
    {
        return new self($reference, $this->joinTable->getName(), $this->parentIdColumn->getName(), $this->relatedIdColumn->getName());
    }

    private function buildJoinTable($name)
    {
        return new Table($name, [$this->parentIdColumn, $this->relatedIdColumn]);
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
        $rows              = $this->reference->syncRelated($context, null, $children);

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

        $parentIdColumn  = $this->parentIdColumn->getName();
        $relatedIdColumn = $this->relatedIdColumn->getName();
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

                /** @var Row $row */
                $row = $childRowMap[Identity::hash($child)];
                if ($row->hasColumn($relatedPrimaryKey)) {
                    $joinRow->setColumn($relatedIdColumn, $row->getColumn($relatedPrimaryKey));
                } else {
                    $row->onInsertPrimaryKey(function ($id) use ($joinRow, $relatedIdColumn) {
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
                        Expr::tableColumn($this->joinTable, $this->parentIdColumn->getName()),
                        Expr::idParam($parent->getColumn($primaryKey))
                );
            }
        }

        return $expressions ? Expr::compoundOr($expressions) : Expr::false();
    }

    /**
     * @param LoadingContext    $context
     * @param ParentChildrenMap $map
     *
     * @return mixed
     */
    public function load(LoadingContext $context, ParentChildrenMap $map)
    {
        // SELECT <related>.*, <parent id> FROM <related>
        // INNER JOIN <join table> ON <join table>.<related key> = <related>.<primary key>
        // WHERE <join table>.<parent key> IN <parent ids>

        $primaryKey = $map->getPrimaryKeyColumn();
        $select     = $this->select();

        $parentIds = [];

        foreach ($map->getAllParents() as $parent) {
            $parentIds[] = Expr::idParam($parent->getColumn($primaryKey));
        }

        $alias = $select->getAliasFor($this->joinTable->getName());
        $select->join(Join::right($this->joinTable, $alias, [
                Expr::equal(Expr::column($alias, $this->relatedIdColumn), $this->column($this->primaryKey))
        ]));

        $parentIdColumn = Expr::column($alias, $this->parentIdColumn);
        $select->where(Expr::in($parentIdColumn, Expr::tuple($parentIds)));
        $select->addColumn($this->parentIdColumn->getName(), $parentIdColumn);

        $indexedGroups = [];
        $parentIdName  = $this->parentIdColumn->getName();

        $rows = $context->query($select)->getRows();
        foreach ($rows as $row) {
            $indexedGroups[$row->getColumn($parentIdName)][] = $row;
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


        $delete        = $parentDelete->copy()->setTable($this->joinTable);
        $alias         = $delete->getAliasFor($parentDelete->getTable()->getName());
        $parentPrimaryKey = Expr::column($alias, $parentDelete->getTable()->getPrimaryKeyColumn());

        $joinOnCondition = Expr::equal(
                $parentPrimaryKey,
                Expr::tableColumn($this->joinTable, $this->parentIdColumn->getName())
        );

        $isSelfReferencing = $parentDelete->getTable()->getName() === $this->mapper->getPrimaryTable()->getName();
        if ($isSelfReferencing) {
            // If the relation is self-referencing the join condition becomes
            // INNER JOIN <parent table> ON  <parent table>.<primary key> = <join table>.<parent key> OR <parent table>.<primary key> = <join table>.<related key>
            $joinOnCondition = Expr::or_($joinOnCondition, Expr::equal(
                    $parentPrimaryKey,
                    Expr::tableColumn($this->joinTable, $this->relatedIdColumn->getName())
            ));
        }

        $relatedDelete = $delete
                ->join(Join::inner($parentDelete->getTable(), $alias, [$joinOnCondition]));

        $context->queue($relatedDelete);
    }
}