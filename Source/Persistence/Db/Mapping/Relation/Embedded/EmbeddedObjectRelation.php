<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Embedded;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildItem;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ReadModelMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation\RelationReadModelReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IEmbeddedToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Row;

/**
 * The embedded object relation class.
 *
 * This will embedded the objects columns in the parent rows.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedObjectRelation extends EmbeddedRelation implements IEmbeddedToOneRelation
{
    /**
     * @var string|null
     */
    private $objectIssetColumnName;

    /**
     * @var bool|null
     */
    private $isWithinValueObject;

    /**
     * @param IEmbeddedObjectMapper $mapper
     * @param string|null           $objectIssetColumnName
     */
    public function __construct(IEmbeddedObjectMapper $mapper, $objectIssetColumnName = null)
    {
        $mapper->initializeRelations();

        $parentColumnsToLoad = $mapper->getMapping()->getAllColumnsToLoad();
        if ($objectIssetColumnName) {
            $parentColumnsToLoad[] = $objectIssetColumnName;
        }

        parent::__construct($mapper, self::DEPENDENT_PARENTS, [], $parentColumnsToLoad);

        $this->objectIssetColumnName = $objectIssetColumnName;
        if ($objectIssetColumnName) {
            // If the column is mapped within the value object
            // then if this column is null, the value object is null
            // If it is withing the parent, it is a boolean column determining
            // whether the object is set or null.
            $this->isWithinValueObject = $mapper->getDefinition()->getTable()->hasColumn($objectIssetColumnName);
        }
    }

    /**
     * @inheritDoc
     */
    public function withReference(IToOneRelationReference $reference)
    {
        if ($reference instanceof RelationReadModelReference) {
            /** @var ReadModelMapper $mapper */
            $mapper = $reference->getMapper();

            return new self($mapper, $this->objectIssetColumnName);
        } else {
            throw NotImplementedException::method(__METHOD__);
        }
    }

    /**
     * @return null|string
     */
    public function getObjectIssetColumnName()
    {
        return $this->objectIssetColumnName;
    }

    /**
     * @return bool|null
     */
    public function issetColumnIsWithinValueObject()
    {
        return $this->isWithinValueObject;
    }

    /**
     * @inheritDoc
     */
    final public function withEmbeddedColumnsPrefixedBy($prefix)
    {
        return new self(
                $this->mapper->withColumnsPrefixedBy($prefix),
                $this->isWithinValueObject ? $prefix . $this->objectIssetColumnName : $this->objectIssetColumnName
        );
    }

    public function deleteBeforeParent(PersistenceContext $context, Delete $parentDelete)
    {
        $this->mapper->deleteFromQueryBeforeParent($context, $parentDelete);
    }

    public function delete(PersistenceContext $context, Delete $parentDelete)
    {
        $this->mapper->deleteFromQuery($context, $parentDelete);
    }

    public function deleteAfterParent(PersistenceContext $context, Delete $parentDelete)
    {
        $this->mapper->deleteFromQueryAfterParent($context, $parentDelete);
    }

    /**
     * @inheritDoc
     */
    public function persistBeforeParent(PersistenceContext $context, ParentChildMap $map)
    {
        list($parentRows, $children) = $this->getDataFromMap($map);

        $this->mapper->persistAllToRowsBeforeParent($context, $children, $parentRows);
    }


    /**
     * @param PersistenceContext $context
     * @param ParentChildMap     $map
     *
     * @return void
     * @throws TypeMismatchException
     */
    public function persist(PersistenceContext $context, ParentChildMap $map)
    {
        /** @var Row[] $parentRows */
        $parentRows = [];
        /** @var object[] $children */
        $children = [];

        foreach ($map->getItems() as $key => $item) {
            $parentRow = $item->getParent();

            $hasChild = (bool)$item->getChild();
            if ($hasChild) {
                $parentRows[$key] = $parentRow;
                $children[$key]   = $item->getChild();
            }

            if ($this->objectIssetColumnName && !$this->isWithinValueObject) {
                $parentRow->setColumn($this->objectIssetColumnName, $hasChild);
            }
        }

        $this->mapper->persistAllToRows($context, $children, $parentRows);
    }

    public function persistAfterParent(PersistenceContext $context, ParentChildMap $map)
    {
        list($parentRows, $children) = $this->getDataFromMap($map);

        $this->mapper->persistAllToRowsAfterParent($context, $children, $parentRows);
    }

    /**
     * @param ParentChildMap $map
     *
     * @return array
     */
    protected function getDataFromMap(ParentChildMap $map)
    {
        /** @var Row[] $parentRows */
        $parentRows = [];
        /** @var object[] $children */
        $children = [];

        foreach ($map->getItems() as $key => $item) {
            if ($item->getChild()) {
                $parentRows[$key] = $item->getParent();
                $children[$key]   = $item->getChild();
            }
        }

        return array($parentRows, $children);
    }

    /**
     * @param LoadingContext $context
     * @param ParentChildMap $map
     *
     * @return void
     */
    public function load(LoadingContext $context, ParentChildMap $map)
    {
        /** @var Row[] $parentRows */
        $parentRows = [];
        /** @var ParentChildItem[] $items */
        $items = [];

        foreach ($map->getItems() as $key => $item) {
            $parentRow = $item->getParent();

            if ($this->objectIssetColumnName) {
                $objectIssetValue = $parentRow->getColumn($this->objectIssetColumnName);
                if ($this->isWithinValueObject && $objectIssetValue === null) {
                    continue;
                } elseif (!$this->isWithinValueObject && !$objectIssetValue) {
                    continue;
                }
            }

            $parentRows[$key] = $parentRow;
            $items[$key]      = $item;
        }

        $objects = $this->mapper->loadAll($context, $parentRows);

        foreach ($objects as $key => $object) {
            $items[$key]->setChild($object);
        }
    }
}