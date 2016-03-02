<?php declare(strict_types = 1);

namespace Dms\Core\Table\DataSource\Definition;

use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Table\ITableStructure;

/**
 * The finalized object table definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedObjectTableDefinition
{
    /**
     * @var FinalizedClassDefinition
     */
    protected $class;

    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * @var string[]
     */
    protected $propertyComponentIdMap;

    /**
     * @var string[]
     */
    protected $indexComponentIdMap;

    /**
     * @var callable[]
     */
    protected $componentIdCallableMap;

    /**
     * @var callable[]
     */
    protected $customCallableMappers;

    /**
     * FinalizedObjectTableDefinition constructor.
     *
     * @param FinalizedClassDefinition $class
     * @param ITableStructure          $structure
     * @param string[]                 $propertyComponentIdMap
     * @param string[]                 $indexComponentIdMap
     * @param callable[]               $componentIdCallableMap
     * @param callable[]               $customCallableMappers
     */
    public function __construct(
        FinalizedClassDefinition $class,
        ITableStructure $structure,
        array $propertyComponentIdMap,
        array $indexComponentIdMap,
        array $componentIdCallableMap,
        array $customCallableMappers
    )
    {
        $this->class                  = $class;
        $this->structure              = $structure;
        $this->propertyComponentIdMap = $propertyComponentIdMap;
        $this->indexComponentIdMap    = $indexComponentIdMap;
        $this->componentIdCallableMap = $componentIdCallableMap;
        $this->customCallableMappers  = $customCallableMappers;

        foreach (array_merge($propertyComponentIdMap, array_keys($componentIdCallableMap)) as $componentId) {
            $this->structure->getColumnAndComponent($componentId);
        }
    }

    /**
     * @param string[] $columnNames
     *
     * @return FinalizedObjectTableDefinition
     */
    public function forColumns(array $columnNames) : FinalizedObjectTableDefinition
    {
        $columns = [];

        foreach ($columnNames as $columnName) {
            $columns[$columnName] = $this->structure->getColumn($columnName);
        }

        $columnNames            = array_fill_keys($columnNames, true);
        $propertyComponentIdMap = [];
        $indexComponentIdMap    = [];
        $componentIdCallableMap = [];

        foreach ($this->propertyComponentIdMap as $property => $componentId) {
            $column = $this->getColumnNameFromComponentId($componentId);

            if (isset($columnNames[$column])) {
                $propertyComponentIdMap[$property] = $componentId;
            }
        }

        foreach ($this->indexComponentIdMap as $componentId) {
            $column = $this->getColumnNameFromComponentId($componentId);

            if (isset($columnNames[$column])) {
                $indexComponentIdMap[] = $componentId;
            }
        }

        foreach ($this->componentIdCallableMap as $componentId => $callable) {
            $column = $this->getColumnNameFromComponentId($componentId);

            if (isset($columnNames[$column])) {
                $componentIdCallableMap[$componentId] = $callable;
            }
        }

        return new self(
            $this->class,
            $this->structure->withColumns($columns),
            $propertyComponentIdMap,
            $indexComponentIdMap,
            $componentIdCallableMap,
            $this->customCallableMappers
        );
    }

    /**
     * @param string $componentId
     *
     * @return string
     */
    private function getColumnNameFromComponentId(string $componentId) : string
    {
        return explode('.', $componentId)[0];
    }

    /**
     * Returns whether this mapping requires object instances to load.
     *
     * @return bool
     */
    public function requiresObjectInstanceForMapping() : bool
    {
        return !empty($this->componentIdCallableMap) || !empty($this->customCallableMappers);
    }

    /**
     * @return FinalizedClassDefinition
     */
    public function getClass() : FinalizedClassDefinition
    {
        return $this->class;
    }

    /**
     * @return ITableStructure
     */
    public function getStructure() : ITableStructure
    {
        return $this->structure;
    }

    /**
     * @return string[]
     */
    public function getPropertyComponentIdMap() : array
    {
        return $this->propertyComponentIdMap;
    }

    /**
     * @return string[]
     */
    public function getIndexComponentIdMap()
    {
        return $this->indexComponentIdMap;
    }

    /**
     * @return callable[]
     */
    public function getComponentIdCallableMap() : array
    {
        return $this->componentIdCallableMap;
    }

    /**
     * @return \callable[]
     */
    public function getCustomCallableMappers() : array
    {
        return $this->customCallableMappers;
    }

    /**
     * @param string $columnName
     *
     * @return string[]
     */
    public function getPropertiesRequiredFor(string $columnName) : array
    {
        $propertyNames = [];

        foreach ($this->propertyComponentIdMap as $propertyName => $componentId) {
            if ($this->getColumnNameFromComponentId($componentId) === $columnName) {
                $propertyNames[$propertyName] = $propertyName;
            }
        }

        return $propertyNames;
    }
}