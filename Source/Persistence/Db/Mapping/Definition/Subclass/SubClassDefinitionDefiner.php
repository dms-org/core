<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Subclass;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The subclass definition definer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubClassDefinitionDefiner extends SubClassDefinerBase
{
    /**
     * Defines the mapper for the subclass according to the supplied callback.
     *
     * The callback will be passed an instance of @see MapperDefinition
     *
     * Example:
     * <code>
     * ->define(function (MapperDefinition $map) {
     *      $map->type(SomeClass::class);
     * });
     * </code>
     *
     * @param callable $subClassMapperDefinitionCallback
     *
     * @return void
     */
    public function define(callable $subClassMapperDefinitionCallback)
    {
        $subclassDefinition = $this->constructSubclassDefinition();
        call_user_func($subClassMapperDefinitionCallback, $subclassDefinition);
        call_user_func($this->callback, $subclassDefinition);
    }

    /**
     * Defines the type of the subclass without any extra properties mappings.
     *
     * @param string $classType
     *
     * @return void
     */
    public function asType($classType)
    {
        $this->define(function (MapperDefinition $map) use ($classType) {
            $map->type($classType);
        });
    }
}