<?php

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
    public function getAll();

    /**
     * {@inheritDoc}
     *
     * @return IUser
     */
    public function get($id);

    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function getAllById(array $ids);

    /**
     * {@inheritDoc}
     *
     * @return IUser|null
     */
    public function tryGet($id);

    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function tryGetAll(array $ids);

    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function matching(ICriteria $criteria);

    /**
     * {@inheritDoc}
     *
     * @return IUser[]
     */
    public function satisfying(ISpecification $specification);
}
