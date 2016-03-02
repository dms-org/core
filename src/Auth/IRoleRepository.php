<?php declare(strict_types = 1);

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
    public function getAll() : array;

    /**
     * {@inheritDoc}
     *
     * @return IRole
     */
    public function get(int $id);

    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function getAllById(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return IRole|null
     */
    public function tryGet(int $id);

    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function tryGetAll(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function matching(ICriteria $criteria) : array;

    /**
     * {@inheritDoc}
     *
     * @return IRole[]
     */
    public function satisfying(ISpecification $specification) : array;
}
