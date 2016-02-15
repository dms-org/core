<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Table\DataSource\Definition\ObjectTableDefinition;
use Dms\Core\Table\DataSource\ObjectTableDataSource;

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
    public function __construct(string $name, callable $callback, IObjectSet $data)
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
     * @return TableViewsDefiner
     */
    public function withStructure(callable $structureDefinitionCallback) : TableViewsDefiner
    {
        /** @var string|TypedObject $objectType */
        $objectType = $this->data->getObjectType();
        $definition = new ObjectTableDefinition($objectType::definition());

        $structureDefinitionCallback($definition);

        return new TableViewsDefiner($this->name, $this->callback, new ObjectTableDataSource(
                $definition->finalize(),
                $this->data
        ));
    }
}