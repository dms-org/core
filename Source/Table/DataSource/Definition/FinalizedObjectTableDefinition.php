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
     * @param string[]                $propertyComponentIdMap
     * @param callable[]              $componentIdCallableMap
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
}