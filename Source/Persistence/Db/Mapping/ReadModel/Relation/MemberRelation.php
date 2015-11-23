<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping\RelationMapping;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentMapBase;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ISeparateToManyTableRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ISeparateToOneTableRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Relation;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The member relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class MemberRelation extends Relation
{
    /**
     * @var RelationMapping
     */
    protected $memberMapping;

    /**
     * @inheritDoc
     */
    public function __construct(RelationMapping $memberMapping)
    {
        $firstRelation = $memberMapping->getFirstRelation();

        parent::__construct(
                $firstRelation->getIdString() . ':' . $memberMapping->getRelation()->getIdString(),
                $memberMapping->getRelation()->getMapper(),
                self::DEPENDENT_CHILDREN,
                [],
                $firstRelation->getParentColumnsToLoad()
        );

        $this->memberMapping = $memberMapping;
    }

    /**
     * @return IRelation
     */
    public function getFirstRelation()
    {
        return $this->memberMapping->getFirstRelation();
    }

    /**
     * @param PersistenceContext $context
     * @param Delete             $parentDelete
     *
     * @throws \Iddigital\Cms\Core\Exception\InvalidOperationException
     */
    final public function delete(PersistenceContext $context, Delete $parentDelete)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    /**
     * @param LoadingContext $context
     * @param ParentMapBase  $map
     *
     * @return Select
     * @throws InvalidArgumentException
     */
    public function loadRelation(LoadingContext $context, ParentMapBase $map)
    {
        $separateTableRelations = $this->memberMapping->getSeperateTableRelations();

        /** @var ISeparateTableRelation|null $firstRelation */
        $firstRelation = array_shift($separateTableRelations);

        if (!$firstRelation) {
            $this->memberMapping->getRelation()->load($context, $map);

            return;
        }

        $relationSelect = $firstRelation->getRelationSelectFromParentRows($map, $parentIdColumnName);

        $tableAlias = $relationSelect->getTableAlias();

        foreach ($separateTableRelations as $relation) {
            $tableAlias = $relation->joinSelectToRelatedTable(
                    $tableAlias,
                    Join::INNER,
                    $relationSelect
            );
        }

        $parentIdColumn = $relationSelect->getAliasColumnMap()[$parentIdColumnName];
        $relationSelect->setColumns(['__parent_id__' => $parentIdColumn]);
        $parentIdColumnName = '__parent_id__';

        $allRelations = $this->memberMapping->getRelationsToSubSelect();
        $lastRelation = end($allRelations);

        if ($lastRelation instanceof ISeparateToOneTableRelation) {
            /** @var ParentChildMap $map */
            $lastRelation->loadFromSelect($context, $map, $relationSelect, $tableAlias, $parentIdColumnName);
        } elseif ($lastRelation instanceof ISeparateToManyTableRelation) {
            /** @var ParentChildrenMap $map */
            $lastRelation->loadFromSelect($context, $map, $relationSelect, $tableAlias, $parentIdColumnName);
        } elseif ($lastRelation instanceof EmbeddedObjectRelation) {
            /** @var ParentChildMap $map */
            $this->loadEmbeddedObjectRelation($context, $map, $lastRelation, $relationSelect, $tableAlias, $parentIdColumnName);
        } else {
            throw InvalidArgumentException::format('Unknown relation type %s', get_class($lastRelation));
        }
    }

    public function loadEmbeddedObjectRelation(
            LoadingContext $context,
            ParentChildMap $map,
            EmbeddedObjectRelation $lastRelation,
            Select $relationSelect,
            $tableAlias,
            $parentIdColumnName
    ) {
        $mapper = $lastRelation->getEmbeddedObjectMapper();

        $mapper->getMapping()->addLoadToSelect($relationSelect, $tableAlias);

        $objectIssetColumnName = $lastRelation->getObjectIssetColumnName();

        if ($objectIssetColumnName) {
            $relationSelect->addColumn(
                    $objectIssetColumnName,
                    Expr::column($tableAlias, $mapper->getRootEntityMapper()->getPrimaryTable()->getColumn($objectIssetColumnName))
            );
        }

        $embeddedObjectMap = new ParentChildMap($parentIdColumnName);
        $lastRelation->load($context, $embeddedObjectMap);

        $idObjectMap = [];

        foreach ($embeddedObjectMap->getItems() as $item) {
            $parentId               = $item->getParent()->getColumn($parentIdColumnName);
            $idObjectMap[$parentId] = $item->getChild();
        }

        $parentPrimaryKey = $map->getPrimaryKeyColumn();
        foreach ($map->getItems() as $item) {
            $parentId = $item->getParent()->getColumn($parentPrimaryKey);

            $item->setChild(isset($idObjectMap[$parentId]) ? $idObjectMap[$parentId] : null);
        }
    }
}