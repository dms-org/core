<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\Criteria;

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
    public function getEntityType();

    /**
     * {@inheritDoc}
     *
     * @return Criteria
     */
    public function criteria();

    /**
     * {@inheritDoc}
     *
     * @return IEntity[]
     */
    public function getAll();

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
    public function has($id);

    /**
     * Returns whether the entities with the given ids are within this collection.
     *
     * @param int[] $ids
     * @return bool
     */
    public function hasAll(array $ids);

    /**
     * Returns the entity with the given id.
     *
     * @param int $id
     * @return IEntity
     * @throws EntityNotFoundException
     */
    public function get($id);

    /**
     * Returns the entity with the given id or null if does not exist.
     *
     * @param int $id
     * @return IEntity|null
     */
    public function tryGet($id);

    /**
     * Returns the entities with the given ids.
     *
     * @param int[] $ids
     * @return IEntity[]
     */
    public function tryGetAll(array $ids);

    /**
     * {@inheritDoc}
     *
     * @return IEntity[]
     * @throws Exception\TypeMismatchException
     */
    public function matching(ICriteria $criteria);

    /**
     * {@inheritDoc}
     *
     * @return IEntity[]
     * @throws Exception\TypeMismatchException
     */
    public function satisfying(ISpecification $specification);
}
