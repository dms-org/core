<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\IEntity;

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
    public function __construct(string $entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * @return string
     */
    public function getEntityType() : string
    {
        return $this->entityType;
    }

    /**
     * @return IEntity[]
     */
    public function getEntities() : array
    {
        return $this->entities;
    }

    /**
     * @param $id
     *
     * @return IEntity|null
     */
    public function get(int $id)
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
     * @param $id
     *
     * @return bool
     */
    public function has(int $id) : bool
    {
        return $this->get($id) !== null;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function remove(int $id) : bool
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
    public function add(IEntity $entity) : bool
    {
        $class = $this->entityType;

        if(!($entity instanceof $class)) {
            throw TypeMismatchException::argument(__METHOD__, 'entity', $class, $entity);
        }

        if ($entity->getId() === null) {
            $this->nullIdEntities[] = $entity;
            return true;
        }

        $id = $entity->getId();

        if ($this->has($id)) {
            return false;
        }

        $this->entities[$id] = $entity;

        return true;
    }
}