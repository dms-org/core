<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

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
     * @param string   $entityType
     * @param string[] $valueObjectProperties
     * @param string   $idPropertyName
     *
     * @return string
     */
    public function loadRelatedEntityType(string $entityType, array $valueObjectProperties, string $idPropertyName) : string;
}