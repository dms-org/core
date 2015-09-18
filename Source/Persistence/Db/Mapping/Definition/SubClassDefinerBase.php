<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

/**
 * The sub class definer base.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SubClassDefinerBase
{
    /**
     * @var MapperDefinition
     */
    protected $parentDefinition;

    /**
     * @var MapperDefinition
     */
    protected $subClassDefinition;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * SubClassDefinerBase constructor.
     *
     * @param MapperDefinition $parentDefinition
     * @param MapperDefinition $subClassDefinition
     * @param callable         $callback
     */
    public function __construct(MapperDefinition $parentDefinition, MapperDefinition $subClassDefinition, callable $callback)
    {
        $this->parentDefinition   = $parentDefinition;
        $this->subClassDefinition = $subClassDefinition;
        $this->callback           = $callback;
    }
}