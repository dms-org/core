<?php

namespace Iddigital\Cms\Core\Persistence;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\ICriteria;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\ISpecification;

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
     * {@inheritDoc}
     */
    public function getElementType()
    {
        return $this->collection->getElementType();
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectType()
    {
        return $this->collection->getObjectType();
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityType()
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
    public function getAll()
    {
        return $this->collection->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return $this->collection->has($id);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAll(array $ids)
    {
        return $this->collection->hasAll($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        return $this->collection->get($id);
    }

    /**
     * {@inheritDoc}
     */
    public function tryGet($id)
    {
        return $this->collection->tryGet($id);
    }

    /**
     * {@inheritDoc}
     */
    public function tryGetAll(array $ids)
    {
        return $this->collection->tryGetAll($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function save(IEntity $entity)
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
    public function remove(IEntity $entity)
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
    public function removeById($id)
    {
        $this->removeAllById([$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAllById(array $ids)
    {
        $ids = array_flip($ids);
        $this->collection->removeWhere(function (IEntity $other) use ($ids) {
            return isset($ids[$other->getId()]);
        });
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
    public function criteria()
    {
        return $this->collection->criteria();
    }

    /**
     * {@inheritDoc}
     */
    public function countMatching(ICriteria $criteria)
    {
        return $this->collection->countMatching($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function matching(ICriteria $criteria)
    {
        return $this->collection->matching($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function satisfying(ISpecification $specification)
    {
        return $this->collection->satisfying($specification);
    }

    public function getIterator()
    {
        return $this->collection->getIterator();
    }
}
