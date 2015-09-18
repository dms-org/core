<?php

namespace Iddigital\Cms\Core\Persistence;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\ICriteria;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\IReadModel;

/**
 * The API for a read model repository.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IReadModelRepository extends IObjectSet
{
    /**
     * Returns the read model type of the repository.
     *
     * @return string
     */
    public function getReadModelType();

    /**
     * {@inheritDoc}
     * @return IReadModel[]
     */
    public function getAll();

    /**
     * {@inheritDoc}
     */
    public function criteria();

    /**
     * {@inheritDoc}
     *
     * @return IReadModel[]
     * @throws Exception\TypeMismatchException
     */
    public function matching(ICriteria $criteria);
}
