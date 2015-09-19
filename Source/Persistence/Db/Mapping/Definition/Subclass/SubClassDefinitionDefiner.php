<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Subclass;

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
     * @param callable $subClassMapperDefinitionCallback
     *
     * @return void
     */
    public function define(callable $subClassMapperDefinitionCallback)
    {
        call_user_func($subClassMapperDefinitionCallback, $this->subClassDefinition);
        call_user_func($this->callback, $this->subClassDefinition);
    }
}