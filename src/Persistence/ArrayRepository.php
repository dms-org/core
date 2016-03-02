<?php declare(strict_types = 1);

namespace Dms\Core\Persistence;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Type\IType;

/**
 * An implementation of the repository using an in-memory store.
 *
 * This can be used for tests and mocking.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ArrayRepository implements IRepository
{
    /**
     * @var EntityCollection
     */
    private $collection;

    /**
     * @var int
     */
    private $maxId = 0;

    /**
     * ArrayRepository constructor.
     *
     * @param EntityCollection $collection
     */
    public function __construct(EntityCollection $collection)
    {
        $this->collection = $collection;

        $this->maxId = $collection->maximum(function (IEntity $entity) {
            return $entity->getId();
        });
    }

    /**
     * @return EntityCollection
     */
    final public function getCollection() : EntityCollection
    {
        return $this->collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getElementType() : IType
    {
        return $this->collection->getElementType();
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectType() : string
    {
        return $this->collection->getObjectType();
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityType() : string
    {
        return $this->collection->getEntityType();
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getAll() : array
    {
        return $this->collection->getAll();
    }

    /**
     * @inheritDoc
     */
    public function getObjectId(ITypedObject $object) : int
    {
        return $this->collection->getObjectId($object);
    }

    /**
     * {@inheritDoc}
     */
    public function has(int $id) : bool
    {
        return $this->collection->has($id);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAll(array $ids) : bool
    {
        return $this->collection->hasAll($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($object)
    {
        return $this->collection->contains($object);
    }

    /**
     * {@inheritDoc}
     */
    public function containsAll(array $objects) : bool
    {
        return $this->collection->containsAll($objects);
    }

    /**
     * {@inheritDoc}
     */
    public function get(int $id)
    {
        return $this->collection->get($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllById(array $ids) : array
    {
        return $this->collection->getAllById($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function tryGet(int $id)
    {
        return $this->collection->tryGet($id);
    }

    /**
     * {@inheritDoc}
     */
    public function tryGetAll(array $ids) : array
    {
        return $this->collection->tryGetAll($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function save(ITypedObject $entity)
    {
        $this->saveAll([$entity]);
    }

    /**
     * {@inheritDoc}
     */
    public function saveAll(array $entities)
    {
        /** @var IEntity[] $entities */
        foreach ($entities as $entity) {
            if ($entity->getId() === null) {
                $entity->setId(++$this->maxId);
            } else {
                $this->maxId = max($this->maxId, $entity->getId());
            }
        }

        $this->collection->addRange($entities);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($entity)
    {
        $this->removeAll([$entity]);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAll(array $entities)
    {
        Exception\InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'entities', $entities, $this->getEntityType());

        $ids = [];
        /** @var IEntity[] $entities */
        foreach ($entities as $entity) {
            if ($entity->getId() !== null) {
                $ids[] = $entity->getId();
            }
        }

        $this->removeAllById($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function removeById(int $id)
    {
        $this->collection->removeById($id);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAllById(array $ids)
    {
        $this->collection->removeAllById($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->collection->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function criteria() : Criteria
    {
        return $this->collection->criteria();
    }

    /**
     * {@inheritDoc}
     */
    public function countMatching(ICriteria $criteria) : int
    {
        return $this->collection->countMatching($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function matching(ICriteria $criteria) : array
    {
        return $this->collection->matching($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function satisfying(ISpecification $specification) : array
    {
        return $this->collection->satisfying($specification);
    }

    public function getIterator()
    {
        return $this->collection->getIterator();
    }
}
