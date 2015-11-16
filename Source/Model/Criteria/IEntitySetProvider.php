<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Model\IEntitySet;

/**
 * The entity set provider interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IEntitySetProvider
{
    /**
     * Loads the data source for the supplied entity type.
     *
     * @param string $entityType
     *
     * @return IEntitySet
     */
    public function loadDataSourceFor($entityType);
}