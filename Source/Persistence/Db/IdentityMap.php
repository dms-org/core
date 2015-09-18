<?php

namespace Iddigital\Cms\Core\Persistence\Db;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\IEntity;

/**
 * The identity map class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IdentityMap
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var IEntity[]
     */
    private $entities = [];

    /**
     * @var IEntity[]
     */
    private $nullIdEntities = [];

    /**
     * @param string $entityType
     */
    public function __construct($entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return IEntity[]
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param int $id
     *
     * @return IEntity|null
     */
    public function get($id)
    {
        if ($id === null) {
            return null;
        }

        if (isset($this->entities[$id])) {
            return $this->entities[$id];
        }

        // Entities are mutable
        // Search through the previously null-id entities
        // in case an id has been set now.
        foreach ($this->nullIdEntities as $entity) {
            if ($entity->getId() === $id) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->get($id) !== null;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function remove($id)
    {
        if ($this->has($id)) {
            unset($this->entities[$id]);

            // Entities are mutable
            // Search through the previously null-id entities
            // in case an id has been set now.
            foreach ($this->nullIdEntities as $key => $entity) {
                if ($entity->getId() === $id) {
                    unset($this->nullIdEntities[$key]);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param IEntity $entity
     *
     * @return bool
     * @throws InvalidArgumentException
     * @throws TypeMismatchException
     */
    public function add(IEntity $entity)
    {
        $class = $this->entityType;

        if(!($entity instanceof $class)) {
            throw TypeMismatchException::argument(__METHOD__, 'entity', $class, $entity);
        }

        if ($entity->getId() === null) {
            $this->nullIdEntities[] = $entity;
        }

        $id = $entity->getId();

        if ($this->has($id)) {
            return false;
        }

        $this->entities[$id] = $entity;

        return true;
    }
}