<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

/**
 * The to one relation class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneRelation extends ToOneRelationBase
{
    /**
     * @var string
     */
    protected $foreignKeyToParent;

    /**
     * @var Column
     */
    protected $foreignKeyColumn;

    /**
     * @param IToOneRelationReference $reference
     * @param string                  $foreignKeyToParent
     * @param IRelationMode           $mode
     *
     * @throws InvalidRelationException
     */
    public function __construct(IToOneRelationReference $reference, $foreignKeyToParent, IRelationMode $mode)
    {
        parent::__construct($reference, $mode, self::DEPENDENT_CHILDREN);
        $this->foreignKeyToParent = $foreignKeyToParent;
        $this->foreignKeyColumn   = $this->mapper->getPrimaryTable()->findColumn($this->foreignKeyToParent);

        if (!$this->foreignKeyColumn) {
            throw InvalidRelationException::format(
                    'Invalid parent foreign key column %s does not exist on related table %s',
                    $this->foreignKeyToParent, $this->mapper->getPrimaryTableName()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function withReference(IToOneRelationReference $reference)
    {
        return new self($reference, $this->foreignKeyToParent, $this->mode);
    }

    public function persist(PersistenceContext $context, ParentChildMap $map)
    {
        if ($map->hasAnyParentsWithPrimaryKeys()) {
            $this->mode->syncInvalidatedRelationsQuery(
                    $context,
                    $this->table,
                    $this->foreignKeyColumn,
                    $this->getInvalidatedRelationExpr($map)
            );
        }

        $this->insertRelated($context, $map);
    }

    protected function deleteByParentQuery(PersistenceContext $context, Delete $parentDelete)
    {
        $this->mode->removeRelationsQuery(
                $context,
                $this->mapper,
                $parentDelete,
                $this->table,
                $this->foreignKeyColumn,
                $parentDelete->getTable()->getPrimaryKeyColumn()
        );
    }

    /**
     * @param PersistenceContext $context
     * @param ParentChildMap     $map
     *
     * @return void
     */
    protected function insertRelated(PersistenceContext $context, ParentChildMap $map)
    {
        $primaryKey = $map->getPrimaryKeyColumn();
        /** @var Row[] $parents */
        $parents = [];
        /** @var array $children */
        $children = [];

        foreach ($map->getItems() as $key => $item) {
            $parents[$key]  = $item->getParent();
            $children[$key] = $item->getChild();
        }

        $rows = $this->reference->syncRelated($context, $this->foreignKeyColumn, $children);

        $selfReferencingChildRows = [];

        foreach ($rows as $key => $row) {
            $parent = $parents[$key];

            if ($parent->hasColumn($primaryKey)) {
                $this->setForeignKey([$row], $this->foreignKeyToParent, $parent->getColumn($primaryKey));
            } else {
                $parent->onInsertPrimaryKey(function ($id) use ($row) {
                    $this->setForeignKey([$row], $this->foreignKeyToParent, $id);
                });

                if ($parent === $row) {
                    $selfReferencingChildRows[] = $row;
                }
            }
        }

        if ($selfReferencingChildRows) {
            // If the rows are self-referencing and need to be inserted
            // an extra step must be taken because the primary key will
            // only be known after inserting so the foreign key to itself
            // will have to be updated separately afterwards
            $context->bulkUpdate(new RowSet($this->table->withColumnsIgnoringConstraints([
                    $this->primaryKey,
                    $this->foreignKeyColumn
            ]), $selfReferencingChildRows));
        }
    }

    protected function getInvalidatedRelationExpr(ParentChildMap $map)
    {
        // For each parent row:
        // foreign_key_to_parent = <parent key> AND primary_key != <current child key>
        $primaryKey  = $map->getPrimaryKeyColumn();
        $expressions = [];

        foreach ($map->getItems() as $item) {
            $parent = $item->getParent();

            if ($parent->hasColumn($primaryKey)) {
                $childId = $this->reference->getIdFromValue($item->getChild());

                $equalsParentForeignKey = Expr::equal(
                        $this->column($this->foreignKeyColumn),
                        Expr::idParam($parent->getColumn($primaryKey))
                );

                if ($childId !== null) {
                    $expressions[] = Expr::and_(
                            $equalsParentForeignKey,
                            Expr::notEqual($this->column($this->primaryKey), Expr::idParam($childId))
                    );
                } else {
                    $expressions[] = $equalsParentForeignKey;
                }
            }
        }

        return $expressions ? Expr::compoundOr($expressions) :  Expr::false();
    }

    /**
     * @param LoadingContext $context
     * @param ParentChildMap $map
     *
     * @return void
     */
    public function load(LoadingContext $context, ParentChildMap $map)
    {
        $primaryKey = $map->getPrimaryKeyColumn();
        $parentIds  = [];

        foreach ($map->getAllParents() as $parent) {
            $parentIds[] = Expr::idParam($parent->getColumn($primaryKey));
        }

        $select = $this->select();
        $select->addRawColumn($this->foreignKeyToParent);
        $select->where(Expr::in($this->column($this->foreignKeyColumn), Expr::tuple($parentIds)));

        $indexedResults = [];

        $rows = $context->query($select)->getRows();
        foreach ($rows as $row) {
            $indexedResults[$row->getColumn($this->foreignKeyToParent)] = $row;
        }

        $values = $this->reference->loadValues($context, $indexedResults);

        foreach ($map->getItems() as $item) {
            $parentKey = $item->getParent()->getColumn($primaryKey);
            $item->setChild(isset($values[$parentKey]) ? $values[$parentKey] : null);
        }
    }
}