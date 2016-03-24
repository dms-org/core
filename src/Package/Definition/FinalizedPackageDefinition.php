<?php declare(strict_types = 1);

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
     * @var array
     */
    private $metadata;

    /**
     * @var string[]|callable[]
     */
    private $nameModuleClassMap;

    /**
     * @var string[]
     */
    private $dashboardWidgetNames;

    /**
     * FinalizedPackageDefinition constructor.
     *
     * @param string              $name
     * @param array               $metadata
     * @param string[]|callable[] $nameModuleClassMap
     * @param string[]            $dashboardWidgetNames
     */
    public function __construct(string $name, array $metadata, array $nameModuleClassMap, array $dashboardWidgetNames)
    {
        $this->name                 = $name;
        $this->metadata             = $metadata;
        $this->nameModuleClassMap   = $nameModuleClassMap;
        $this->dashboardWidgetNames = $dashboardWidgetNames;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getMetadata() : array
    {
        return $this->metadata;
    }

    /**
     * @return string[]|callable[]
     */
    public function getNameModuleClassMap() : array
    {
        return $this->nameModuleClassMap;
    }

    /**
     * @return string[]
     */
    public function getDashboardWidgetNames() : array
    {
        return $this->dashboardWidgetNames;
    }
}