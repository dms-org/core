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
    const ENTITY_WITHOUT_ID_PREFIX = '__new_';

    /**
     * @var IEntity[]|null
     */
    protected $identityMap;


    /**
     * @var \SplObjectStorage|null
     */
    protected $instanceMap;

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

        $this->loadIdentityMap($this->toOrderedMap());
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
        $this->instanceMap = new \SplObjectStorage();

        /** @var IEntity $entity */
        $entityWithIdIndex = 0;
        foreach ($elements->values() as $entity) {
            if ($entity->hasId()) {
                $id = $entity->getId();
            } else {
                $id = self::ENTITY_WITHOUT_ID_PREFIX . $entityWithIdIndex++;
            }

            $this->identityMap[$id]     = $entity;
            $this->instanceMap[$entity] = $id;
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

        foreach ($objects as $object) {
            if ($object->hasId()) {
                if (!isset($this->identityMap[$object->getId()])) {
                    return false;
                }
            } else {
                if (!isset($this->instanceMap[$object])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getObjectId(ITypedObject $object)
    {
        $this->toOrderedMap();
        $objectType = $this->getObjectType();

        if (!($object instanceof $objectType)) {
            throw TypeMismatchException::argument(__METHOD__, 'object', $objectType, $object);
        }

        /** @var IEntity $object */
        if (!$this->doesContainsObjects([$object])) {
            throw InvalidArgumentException::format(
                    'The supplied entity of type %s is not contained within the collection',
                    get_class($object)
            );
        }

        return $this->getObjectIdInternal($object);
    }

    protected function getObjectIdInternal(IEntity $object)
    {
        /** @var IEntity $object */
        if ($object->hasId()) {
            return $object->getId();
        } else {
            return $this->instanceMap[$object];
        }
    }

    public function offsetSet($index, $value)
    {
        parent::offsetSet($index, $value);
        $this->loadIdentityMap($this->toOrderedMap());
    }

    /**
     * {@inheritDoc}
     */
    public function removeById($id)
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
            return isset($ids[$this->getObjectIdInternal($other)]);
        });
        $this->loadIdentityMap($this->toOrderedMap());
    }

    /**
     * {@inheritDoc}
     */
    public function has($id) : bool
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
    public function getAllById(array $ids) : array
    {
        $this->toOrderedMap();

        $entities = [];
        foreach ($ids as $id) {
            if (isset($this->identityMap[$id])) {
                $entities[$id] = $this->identityMap[$id];
            } else {
                throw new EntityNotFoundException($this->elementType->getClass(), $id);
            }
        }

        return $entities;
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
    public function tryGetAll(array $ids) : array
    {
        $this->toOrderedMap();

        return array_intersect_key($this->identityMap, array_flip($ids));
    }
}
