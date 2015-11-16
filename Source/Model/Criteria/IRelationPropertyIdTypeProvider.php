<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntitySet;

/**
 * The relation property type provider
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IRelationPropertyIdTypeProvider
{
    /**
     * Loads the data source for the supplied entity type.
     *
     * @param string $entityType
     * @param string $idPropertyName
     *
     * @return IEntitySet
     * @throws InvalidArgumentException
     */
    public function loadRelatedEntityType($entityType, $idPropertyName);
}