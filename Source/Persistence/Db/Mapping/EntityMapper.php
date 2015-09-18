<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The entity mapper base class.
 *
 * This class maps typed objects.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EntityMapper extends EntityMapperBase
{
    /**
     * EntityMapper constructor.
     *
     * @param string $tableName
     */
    public function __construct($tableName)
    {
        $definition = new MapperDefinition();
        $this->define($definition);
        parent::__construct($definition, $tableName);
    }

    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    abstract protected function define(MapperDefinition $map);
}