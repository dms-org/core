<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

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
    protected $foreignKeyColumn;

    /**
     * @param IToManyRelationReference $reference
     * @param string                   $parentForeignKey
     * @param IRelationMode            $mode
     *
     * @throws InvalidRelationException
     */
    public function __construct(IToManyRelationReference $reference, $parentForeignKey, IRelationMode $mode)
    {
        parent::__construct($reference, $mode, self::DEPENDENT_CHILDREN);
        $this->foreignKeyToParent = $parentForeignKey;
        $this->mapper->onInitialized(function () {
            $this->foreignKeyColumn   = $this->mapper->getPrimaryTable()->getColumn($this->foreignKeyToParent);

            if (!$this->foreignKeyColumn) {
                throw InvalidRelationException::format(
                        'Invalid parent foreign key column %s does not exist on related table %s',
                        $this->foreignKeyToParent, $this->mapper->getPrimaryTable()->getName()
                );
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function withReference(IToManyRelationReference $reference)
    {
        return new self($reference, $this->foreignKeyToParent, $this->mode);
    }

    public function persist(PersistenceContext $context, ParentChildrenMap $map)
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
     * @param ParentChildrenMap  $map
     *
     * @return void
     */
    protected function insertRelated(PersistenceContext $context, ParentChildrenMap $map)
    {
        $primaryKey = $map->getPrimaryKeyColumn();
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

        $rows = $this->reference->syncRelated($context, $this->foreignKeyColumn, $children);
        $rowGroups = [];

        foreach ($childKeyParentMap as $rowKey => $parentKey) {
            $rowGroups[$parentKey][] = $rows[$rowKey];
        }

        foreach ($map->getItems() as $key => $item) {
            $parentRow = $item->getParent();
            $childRows = isset($rowGroups[$key]) ? $rowGroups[$key] : [];

            if ($parentRow->hasColumn($primaryKey)) {
                $this->setForeignKey($childRows, $this->foreignKeyToParent, $parentRow->getColumn($primaryKey));
            } else {
                $parentRow->onInsertPrimaryKey(function ($id) use ($childRows) {
                    $this->setForeignKey($childRows, $this->foreignKeyToParent, $id);
                });
            }
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
                        $this->column($this->foreignKeyColumn),
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
                            Expr::notIn($this->column($this->table->getPrimaryKeyColumn()), Expr::tuple($childrenIds))
                    );
                } else {
                    $expressions[] = $equalsParentForeignKey;
                }
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
        $primaryKey = $map->getPrimaryKeyColumn();
        $parentIds  = [];

        foreach ($map->getAllParents() as $parent) {
            $parentIds[] = Expr::idParam($parent->getColumn($primaryKey));
        }

        $select = $this->select();
        $select->addRawColumn($this->foreignKeyToParent);
        $select->where(Expr::in($this->column($this->foreignKeyColumn), Expr::tuple($parentIds)));

        $indexedGroups = [];

        $rows = $context->query($select)->getRows();
        foreach ($rows as $row) {
            $indexedGroups[$row->getColumn($this->foreignKeyToParent)][] = $row;
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
}