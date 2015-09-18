<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelationAliasDefiner
{
    /**
     * @var ReadMapperDefinition
     */
    private $definition;
    /**
     * @var callable
     */
    private $callback;

    /**
     * RelationAliasDefiner constructor.
     *
     * @param ReadMapperDefinition $definition
     * @param callable           $callback
     */
    public function __construct(ReadMapperDefinition $definition, callable $callback)
    {
        $this->definition = $definition;
        $this->callback = $callback;
    }

    /**
     * Defines the property name on the read model to map the results to.
     *
     * @param string $propertyName
     *
     * @return RelationLoadingDefiner
     */
    public function to($propertyName)
    {
        return new RelationLoadingDefiner($this->definition, $propertyName, $this->callback);
    }
}