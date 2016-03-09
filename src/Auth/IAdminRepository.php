<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Exception;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ISpecification;
use Dms\Core\Persistence\IRepository;

interface IAdminRepository extends IRepository
{
    /**
     * {@inheritDoc}
     *
     * @return IAdmin[]
     */
    public function getAll() : array;

    /**
     * {@inheritDoc}
     *
     * @return IAdmin
     */
    public function get($id);

    /**
     * {@inheritDoc}
     *
     * @return IAdmin[]
     */
    public function getAllById(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return IAdmin|null
     */
    public function tryGet($id);

    /**
     * {@inheritDoc}
     *
     * @return IAdmin[]
     */
    public function tryGetAll(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return IAdmin[]
     */
    public function matching(ICriteria $criteria) : array;

    /**
     * {@inheritDoc}
     *
     * @return IAdmin[]
     */
    public function satisfying(ISpecification $specification) : array;
}
