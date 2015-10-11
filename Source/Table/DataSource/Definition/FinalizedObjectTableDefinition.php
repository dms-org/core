<?php

namespace Iddigital\Cms\Core\Table\DataSource\Definition;

use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Table\ITableStructure;

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
     * @param callable[]               $componentIdCallableMap
     * @param callable[]               $customCallableMappers
     */
    public function __construct(
            FinalizedClassDefinition $class,
            ITableStructure $structure,
            array $propertyComponentIdMap,
            array $componentIdCallableMap,
            array $customCallableMappers
    ) {
        $this->class                  = $class;
        $this->structure              = $structure;
        $this->propertyComponentIdMap = $propertyComponentIdMap;
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
    public function forColumns(array $columnNames)
    {
        $columns = [];

        foreach ($columnNames as $columnName) {
            $columns[$columnName] = $this->structure->getColumn($columnName);
        }

        $columnNames = array_fill_keys($columnNames, true);


        $propertyComponentIdMap = [];

        foreach ($this->propertyComponentIdMap as $property => $componentId) {
            $column = $this->getColumnNameFromComponentId($componentId);

            if (isset($columnNames[$column])) {
                $propertyComponentIdMap[$property] = $componentId;
            }
        }

        $componentIdCallableMap = [];

        foreach ($this->componentIdCallableMap as $componentId => $callable) {
            $column = $this->getColumnNameFromComponentId($componentId);

            if (isset($columnNames[$column])) {
                $propertyComponentIdMap[$componentId] = $callable;
            }
        }

        return new self(
                $this->class,
                $this->structure->withColumns($columns),
                $propertyComponentIdMap,
                $componentIdCallableMap,
                $this->customCallableMappers
        );
    }

    /**
     * @param string $componentId
     *
     * @return string
     */
    private function getColumnNameFromComponentId($componentId)
    {
        return explode('.', $componentId)[0];
    }

    /**
     * Returns whether this mapping requires object instances to load.
     *
     * @return bool
     */
    public function requiresObjectInstanceForMapping()
    {
        return !empty($this->componentIdCallableMap) || !empty($this->customCallableMappers);
    }

    /**
     * @return FinalizedClassDefinition
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return ITableStructure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return string[]
     */
    public function getPropertyComponentIdMap()
    {
        return $this->propertyComponentIdMap;
    }

    /**
     * @return callable[]
     */
    public function getComponentIdCallableMap()
    {
        return $this->componentIdCallableMap;
    }

    /**
     * @return \callable[]
     */
    public function getCustomCallableMappers()
    {
        return $this->customCallableMappers;
    }

    /**
     * @param string $columnName
     *
     * @return string[]
     */
    public function getPropertiesRequiredFor($columnName)
    {
        $propertyNames = [];

        foreach ($this->propertyComponentIdMap as $propertyName => $componentId) {
            if($this->getColumnNameFromComponentId($componentId) === $columnName) {
                $propertyNames[$propertyName] = $propertyName;
            }
        }

        return $propertyNames;
    }
}