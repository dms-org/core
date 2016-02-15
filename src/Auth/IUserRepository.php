<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Exception;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ISpecification;
use Dms\Core\Persistence\IRepository;

interface IUserRepository extends IRepository
{
    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function getAll() : array;

    /**
     * {@inheritDoc}
     *
     * @return IUser
     */
    public function get(int $id) : \Dms\Core\Model\IEntity;

    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function getAllById(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return IUser|null
     */
    public function tryGet(int $id);

    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function tryGetAll(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function matching(ICriteria $criteria) : array;

    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function satisfying(ISpecification $specification) : array;
}
