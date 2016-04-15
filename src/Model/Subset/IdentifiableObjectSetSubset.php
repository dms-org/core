<?php declare(strict_types = 1);

namespace Dms\Core\Model\Subset;

use Dms\Core\Exception;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\ObjectNotFoundException;

/**
 * The identifiable object subset class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IdentifiableObjectSetSubset extends ObjectSetSubset implements IIdentifiableObjectSet
{
    /**
     * @var IIdentifiableObjectSet
     */
    protected $fullObjectSet;

    /**
     * @inheritDoc
     */
    public function __construct(IIdentifiableObjectSet $fullObjectSet, ICriteria $criteria)
    {
        parent::__construct($fullObjectSet, $criteria);
    }

    /**
     * @inheritDoc
     */
    public function getObjectId(ITypedObject $object)
    {
        return $this->fullObjectSet->getObjectId($object);
    }

    /**
     * @inheritDoc
     */
    public function has($id) : bool
    {
        return $this->hasAll([$id]);
    }

    /**
     * @inheritDoc
     */
    public function hasAll(array $ids) : bool
    {
        if ($this->fullObjectSet instanceof IEntitySet) {
            return $this->countMatching(
                $this->criteria()
                    ->whereIn(Entity::ID, $ids)
            ) === count($ids);
        } else {
            return count($this->tryGetAll($ids)) === count($ids);
        }
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->getAllById([0 => $id])[0];
    }

    /**
     * @inheritDoc
     */
    public function getAllById(array $ids) : array
    {
        $objects = $this->tryGetAll($ids);

        if (count($objects) !== count($ids)) {
            $idLookup = [];

            foreach ($objects as $object) {
                $idLookup[$this->getObjectId($object)] = true;
            }

            foreach ($ids as $id) {
                if (!isset($idLookup[$id])) {
                    if ($this->fullObjectSet instanceof IEntitySet) {
                        throw new EntityNotFoundException($this->getObjectType(), $id);
                    } else {
                        throw new ObjectNotFoundException($this->getObjectType(), $id);
                    }
                }
            }
        }

        return $objects;
    }

    /**
     * @inheritDoc
     */
    public function tryGet($id)
    {
        $results = $this->tryGetAll([$id]);

        return $results ? reset($results) : null;
    }

    /**
     * @inheritDoc
     */
    public function tryGetAll(array $ids) : array
    {
        $objectLookup = [];

        if ($this->fullObjectSet instanceof IEntitySet) {
            $objects      = $this->matching(
                $this->criteria()
                    ->whereIn(Entity::ID, $ids)
            );
            
            foreach ($objects as $object) {
                $objectLookup[$object->getId()] = $object;
            }

        } else {
            $objects = $this->getAll();

            foreach ($objects as $object) {
                $objectLookup[$this->getObjectId($object)] = $object;
            }
        }

        $filteredObjects = [];
        foreach ($ids as $idKey => $id) {
            if (isset($objectLookup[$id])) {
                $filteredObjects[$idKey] = $objectLookup[$id];
            }
        }

        return $filteredObjects;
    }
}