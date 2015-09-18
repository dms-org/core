<?php

namespace Iddigital\Cms\Core\Persistence;

use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Exception;

/**
 * The API for a repository.
 * 
 * The repository acts as an abstraction over the data source for
 * a set of entities.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IRepository extends IEntitySet
{
    /**
     * Returns the entity type of the repository.
     * 
     * @return string
     */
    public function getEntityType();
    
    /**
     * {@inheritDoc}
     */
    public function getAll();

    /**
     * {@inheritDoc}
     */
    public function has($id);

    /**
     * {@inheritDoc}
     */
    public function hasAll(array $ids);

    /**
     * {@inheritDoc}
     */
    public function get($id);
    
    /**
     * {@inheritDoc}
     */
    public function tryGet($id);

    /**
     * {@inheritDoc}
     */
    public function tryGetAll(array $ids);
    
    /**
     * Saves the supplied entity to the underlying data source.
     * 
     * @param IEntity $entity
     * @return void
     */
    public function save(IEntity $entity);
    
    /**
     * Saves the supplied entities to the underlying data source.
     * 
     * @param IEntity[] $entities
     * @return void
     */
    public function saveAll(array $entities);

    /**
     * Removes the supplied entity from the underlying data source.
     *
     * @param IEntity $entity
     * @return void
     */
    public function remove(IEntity $entity);

    /**
     * Removes the entity with the supplied id from the underlying data source.
     *
     * @param int $id
     * @return void
     */
    public function removeById($id);
    
    /**
     * Removes the supplied entities from the underlying data source.
     * 
     * @param IEntity[] $entities
     * @return void
     */
    public function removeAll(array $entities);

    /**
     * Removes the entities with the supplied ids from the underlying data source.
     *
     * @param int[] $ids
     * @return void
     */
    public function removeAllById(array $ids);

    /**
     * Removes all the entities from the underlying data source.
     *
     * @return void
     */
    public function clear();
}
