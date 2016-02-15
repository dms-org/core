<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Model\IEntitySet;

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
    public function loadDataSourceFor(string $entityType) : \Dms\Core\Model\IEntitySet;
}