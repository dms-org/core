<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IEntitySet;

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
     * @return string
     * @throws InvalidArgumentException
     */
    public function loadRelatedEntityType(string $entityType, string $idPropertyName) : string;
}