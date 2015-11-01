<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Pinq\Iterators\IIteratorScheme;
use Pinq\Iterators\IOrderedMap;

/**
 * The entity collection class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class EntityCollection extends ObjectCollection implements IEntityCollection
{
    /**
     * @var IEntity[]|null
     */
    protected $identityMap;

    /**
     * @param string               $entityType
     * @param IEntity[]            $entities
     * @param IIteratorScheme|null $scheme
     * @param Collection|null      $source
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
            $entityType,
            $entities = [],
            IIteratorScheme $scheme = null,
            Collection $source = null
    ) {
        if (!is_a($entityType, IEntity::class, true)) {
            throw Exception\InvalidArgumentException::format(
                    'Invalid entity class: expecting instance of %s, %s given',
                    IEntity::class, $entityType
            );
        }

        parent::__construct($entityType, $entities, $scheme, $source);
    }

    protected function updateElements(\Traversable $elements)
    {
        parent::updateElements($elements);

        $this->loadIdentityMap($this->elements);
    }

    protected function toOrderedMap()
    {
        $elements = parent::toOrderedMap();

        if ($this->identityMap === null) {
            $this->loadIdentityMap($elements);
        }

        return $elements;
    }

    protected function loadIdentityMap(IOrderedMap $elements)
    {
        $this->identityMap = [];

        /** @var IEntity $entity */
        foreach ($elements->values() as $entity) {
            $this->identityMap[$entity->getId() ?: spl_object_hash($entity)] = $entity;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityType()
    {
        return $this->elementType->getClass();
    }

    /**
     * Performs an identity or id comparison to check of objects are equal.
     *
     * @param IEntity[] $objects
     *
     * @return bool
     */
    protected function doesContainsObjects(array $objects)
    {
        $this->toOrderedMap();

        $objectsLookup = new \SplObjectStorage();

        foreach ($this->elements as $object) {
            $objectsLookup[$object] = true;
        }

        foreach ($objects as $object) {
            if (!isset($objectsLookup[$object])) {
                $id = $object->getId();

                if ($id === null || !isset($this->identityMap[$id])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        $this->toOrderedMap();

        return isset($this->identityMap[$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAll(array $ids)
    {
        $this->toOrderedMap();

        foreach ($ids as $id) {
            if (!isset($this->identityMap[$id])) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        $this->toOrderedMap();

        if (isset($this->identityMap[$id])) {
            return $this->identityMap[$id];
        }

        throw new EntityNotFoundException($this->elementType->getClass(), $id);
    }

    /**
     * {@inheritDoc}
     */
    public function tryGet($id)
    {
        $this->toOrderedMap();

        return isset($this->identityMap[$id]) ? $this->identityMap[$id] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function tryGetAll(array $ids)
    {
        $this->toOrderedMap();

        return array_intersect_key($this->identityMap, array_flip($ids));
    }
}
