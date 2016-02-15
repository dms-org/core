<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Criteria;

/**
 * The entity set interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IEntitySet extends IObjectSet
{
    /**
     * Returns the entity type of the entity set.
     *
     * @return string
     */
    public function getEntityType() : string;

    /**
     * {@inheritDoc}
     *
     * @return Criteria
     */
    public function criteria() : Criteria;

    /**
     * {@inheritDoc}
     *
     * @return IEntity[]
     */
    public function getAll() : array;

    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public function count();

    /**
     * Returns whether the entity with the given id is within this collection.
     *
     * @param int $id
     * @return bool
     */
    public function has(int $id) : bool;

    /**
     * Returns whether the entities with the given ids are within this collection.
     *
     * @param int[] $ids
     * @return bool
     */
    public function hasAll(array $ids) : bool;

    /**
     * Returns the entity with the given id.
     *
     * @param int $id
     * @return IEntity
     * @throws EntityNotFoundException
     */
    public function get(int $id) : IEntity;

    /**
     * Returns the entities with the given ids.
     *
     * @param int[] $ids
     * @return IEntity[]
     * @throws EntityNotFoundException
     */
    public function getAllById(array $ids) : array;

    /**
     * Returns the entity with the given id or null if does not exist.
     *
     * @param int $id
     * @return IEntity|null
     */
    public function tryGet(int $id);

    /**
     * Returns the entities with the given ids.
     *
     * @param int[] $ids
     * @return IEntity[]
     */
    public function tryGetAll(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return IEntity[]
     * @throws Exception\TypeMismatchException
     */
    public function matching(ICriteria $criteria) : array;

    /**
     * {@inheritDoc}
     *
     * @return IEntity[]
     * @throws Exception\TypeMismatchException
     */
    public function satisfying(ISpecification $specification) : array;
}
