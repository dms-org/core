<?php

namespace Iddigital\Cms\Core\Package;

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
     * FinalizedPackageDefinition constructor.
     *
     * @param string    $name
     * @param string[] $nameModuleClassMap
     */
    public function __construct($name, array $nameModuleClassMap)
    {
        $this->name = $name;
        $this->nameModuleClassMap = $nameModuleClassMap;
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
}