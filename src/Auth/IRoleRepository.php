<?php

namespace Dms\Core\Auth;

use Dms\Core\Exception;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ISpecification;
use Dms\Core\Persistence\IRepository;

interface IRoleRepository extends IRepository
{
    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function getAll();

    /**
     * {@inheritDoc}
     *
     * @return IRole
     */
    public function get($id);

    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function getAllById(array $ids);

    /**
     * {@inheritDoc}
     *
     * @return IRole|null
     */
    public function tryGet($id);

    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function tryGetAll(array $ids);

    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function matching(ICriteria $criteria);

    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function satisfying(ISpecification $specification);
}
