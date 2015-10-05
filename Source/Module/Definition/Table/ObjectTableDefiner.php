<?php

namespace Iddigital\Cms\Core\Module\Definition\Table;

use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Table\DataSource\Definition\ObjectTableDefinition;
use Iddigital\Cms\Core\Table\DataSource\ObjectTableDataSource;

/**
 * The object table definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectTableDefiner extends TableDefinerBase
{
    /**
     * @var IObjectSet
     */
    private $data;

    /**
     * ObjectTableDefiner constructor.
     *
     * @param string     $name
     * @param callable   $callback
     * @param IObjectSet $data
     */
    public function __construct($name, callable $callback, IObjectSet $data)
    {
        parent::__construct($name, $callback);
        $this->data = $data;
    }

    /**
     * Defines the structure of the table using the table object definition
     * mapping class.
     *
     * Example:
     * <code>
     * ->withStructure(function (ObjectTableDefinition $map) {
     *      $map->property('foo')->to(...);
     * })
     * </code>
     *
     * @see ObjectTableDefinition
     *
     * @param callable $structureDefinitionCallback
     *
     * @return void
     */
    public function withStructure(callable $structureDefinitionCallback)
    {
        /** @var string|TypedObject $objectType */
        $objectType = $this->data->getObjectType();
        $definition = new ObjectTableDefinition($objectType::definition());

        $structureDefinitionCallback($definition);

        call_user_func($this->callback, new ObjectTableDataSource(
                $this->name,
                $definition->finalize(),
                $this->data
        ));
    }
}