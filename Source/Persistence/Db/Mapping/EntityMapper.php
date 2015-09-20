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
     * @param IOrm   $orm
     * @param string|null $tableName
     */
    public function __construct(IOrm $orm, $tableName = null)
    {
        $definition = new MapperDefinition($orm);
        $this->define($definition);
        parent::__construct($definition->finalize($tableName));
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