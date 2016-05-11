<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Embedded;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\ParentChildItem;
use Dms\Core\Persistence\Db\Mapping\ParentChildMap;
use Dms\Core\Persistence\Db\Mapping\ReadModel\ReadModelMapper;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\RelationReadModelReference;
use Dms\Core\Persistence\Db\Mapping\Relation\IEmbeddedToOneRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;

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
     * @param string                $idString
     * @param IEmbeddedObjectMapper $mapper
     * @param string|null           $objectIssetColumnName
     */
    public function __construct(string $idString, IEmbeddedObjectMapper $mapper, string $objectIssetColumnName = null)
    {
        $mapper->initializeRelations();

        $parentColumnsToLoad = $mapper->getMapping()->getAllColumnsToLoad();
        if ($objectIssetColumnName) {
            $parentColumnsToLoad[] = $objectIssetColumnName;
        }

        $this->objectIssetColumnName = $objectIssetColumnName;
        $parentTable                 = $mapper->getDefinition()->getTable();
        if ($objectIssetColumnName) {
            // If the column is mapped within the value object
            // then if this column is null, the value object is null
            // If it is withing the parent, it is a boolean column determining
            // whether the object is set or null.
            $this->isWithinValueObject = $parentTable->hasColumn($objectIssetColumnName);
        }

        $valueType = Type::object($mapper->getObjectType());

        if ($objectIssetColumnName) {
            if ($this->isWithinValueObject && $parentTable->getColumn($objectIssetColumnName)->getType()->isNullable() || !$this->isWithinValueObject) {
                $valueType = $valueType->nullable();
            }
        }

        parent::__construct($idString, $valueType, $mapper, self::DEPENDENT_PARENTS, [], $parentColumnsToLoad);

    }

    /**
     * @inheritDoc
     */
    public function withReference(IToOneRelationReference $reference)
    {
        if ($reference instanceof RelationReadModelReference) {
            /** @var ReadModelMapper $mapper */
            $mapper = $reference->getMapper();

            return new self($this->idString, $mapper, $this->objectIssetColumnName);
        } else {
            throw NotImplementedException::method(__METHOD__);
        }
    }

    /**
     * @return string|null
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
    final public function withEmbeddedColumnsPrefixedBy(string $prefix)
    {
        return new self(
                $this->idString,
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
    protected function getDataFromMap(ParentChildMap $map) : array
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

    protected function getRequiredColumnLookup() : array
    {
        $lookup = [];

        foreach ($this->parentColumnsToLoad as $parentColumn) {
            if (!$this->mapper->getTableWhichThisIsEmbeddedWithin()->getColumn($parentColumn)->getType()->isNullable()) {
                $lookup[$parentColumn] = true;
            }
        }

        return $lookup;
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

        $requiredColumnLookup = $this->getRequiredColumnLookup();

        foreach ($map->getItems() as $key => $item) {
            $parentRow = $item->getParent();

            if ($this->objectIssetColumnName) {
                $objectIssetValue = $parentRow->getColumn($this->objectIssetColumnName);
                if ($this->isWithinValueObject && $objectIssetValue === null) {
                    continue;
                } elseif (!$this->isWithinValueObject && !$objectIssetValue) {
                    continue;
                }
            } else {
                // In case this relation was loaded via a LEFT JOIN the columns may be null
                // even if they are not nullable hence we must only load the object if all the non nullable columns
                // does not contain a null value.
                foreach ($this->parentColumnsToLoad as $requiredColumn) {
                    if (isset($requiredColumnLookup[$requiredColumn]) && $parentRow->getColumn($requiredColumn) === null) {
                        continue 2;
                    }
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