<?php

namespace Dms\Core\Package\Definition;

/**
 * The finalized package definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedPackageDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $nameModuleClassMap;

    /**
     * @var string[]
     */
    private $dashboardWidgetNames;

    /**
     * FinalizedPackageDefinition constructor.
     *
     * @param string   $name
     * @param string[] $nameModuleClassMap
     * @param string[] $dashboardWidgetNames
     */
    public function __construct($name, array $nameModuleClassMap, array $dashboardWidgetNames)
    {
        $this->name                 = $name;
        $this->nameModuleClassMap   = $nameModuleClassMap;
        $this->dashboardWidgetNames = $dashboardWidgetNames;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \string[]
     */
    public function getNameModuleClassMap()
    {
        return $this->nameModuleClassMap;
    }

    /**
     * @return string[]
     */
    public function getDashboardWidgetNames()
    {
        return $this->dashboardWidgetNames;
    }
}