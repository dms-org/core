<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
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
     * @param string                 $entityType
     * @param \Traversable|IEntity[] $entities
     * @param IIteratorScheme|null   $scheme
     * @param Collection|null        $source
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        string $entityType,
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
    public function getEntityType() : string
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
    protected function doesContainsObjects(array $objects) : bool
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
     * @inheritDoc
     */
    public function getObjectId(ITypedObject $object) : int
    {
        $objectType = $this->getObjectType();

        if (!($object instanceof $objectType)) {
            throw TypeMismatchException::argument(__METHOD__, 'object', $objectType, $object);
        }

        /** @var IEntity $object */
        if (!$object->hasId()) {
            throw InvalidArgumentException::format('The supplied entity of type %s does not have an id', get_class($object));
        }

        return $object->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function removeById(int $id)
    {
        $this->removeAllById([$id]);
    }

    /**
     * @inheritDoc
     */
    public function removeAllById(array $ids)
    {
        $ids = array_flip($ids);

        $this->removeWhere(function (IEntity $other) use ($ids) {
            return isset($ids[$other->getId()]);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function has(int $id) : bool
    {
        $this->toOrderedMap();

        return isset($this->identityMap[$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAll(array $ids) : bool
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
    public function get(int $id)
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
    public function getAllById(array $ids) : array
    {
        $this->toOrderedMap();

        $entities = [];
        foreach ($ids as $id) {
            if (isset($this->identityMap[$id])) {
                $entities[] = $this->identityMap[$id];
            } else {
                throw new EntityNotFoundException($this->elementType->getClass(), $id);
            }
        }

        return $entities;
    }

    /**
     * {@inheritDoc}
     */
    public function tryGet(int $id)
    {
        $this->toOrderedMap();

        return isset($this->identityMap[$id]) ? $this->identityMap[$id] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function tryGetAll(array $ids) : array
    {
        $this->toOrderedMap();

        return array_intersect_key($this->identityMap, array_flip($ids));
    }
}
