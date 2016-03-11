<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Table\DataSource\Definition\GroupedTableDefinition;
use Dms\Core\Table\DataSource\Definition\ObjectTableDefinition;
use Dms\Core\Table\DataSource\GroupedTableDataSourceAdapter;
use Dms\Core\Table\DataSource\ObjectTableDataSource;
use Dms\Core\Table\ITableDataSource;

/**
 * The grouped table definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GroupedTableDefiner extends TableDefinerBase
{
    /**
     * @var ITableDataSource
     */
    private $data;

    /**
     * ObjectTableDefiner constructor.
     *
     * @param string     $name
     * @param callable   $callback
     * @param ITableDataSource $data
     */
    public function __construct(string $name, callable $callback, ITableDataSource $data)
    {
        parent::__construct($name, $callback);
        $this->data = $data;
    }

    /**
     * Defines the structure of the table using the grouped table definition
     * mapping class.
     *
     * Example:
     * <code>
     * ->withStructure(function (GroupedTableDefinition $map) {
     *      $map->groupedBy(...);
     *
     *      $map->computed(...)->to(...);
     *      $map->sum(...)->to(...);
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
        $definition = new GroupedTableDefinition($this->data);

        $structureDefinitionCallback($definition);

        return new TableViewsDefiner($this->name, $this->callback, new GroupedTableDataSourceAdapter(
                $definition->finalize()
        ));
    }
}