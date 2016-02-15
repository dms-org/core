<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\ReadModel\Definition;

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
     * @param string|callable $propertyName
     *
     * @return RelationLoadingDefiner
     */
    public function to($propertyName) : RelationLoadingDefiner
    {
        return new RelationLoadingDefiner($this->definition, $propertyName, $this->callback);
    }
}