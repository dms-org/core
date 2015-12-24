<?php

namespace Dms\Core;

/**
 * The cms definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CmsDefinition
{
    /**
     * @var string[]
     */
    protected $namePackageMap = [];

    /**
     * CmsDefinition constructor.
     */
    public function __construct()
    {
    }

    /**
     * Defines the list of installed packages within this cms.
     *
     * Example:
     * <code>
     * $cms->packages([
     *      'some-package-name' => SomePackage::class,
     * ]);
     * </code>
     *
     * @param array $namePackageMap
     *
     * @return void
     */
    public function packages(array $namePackageMap)
    {
        $this->namePackageMap += $namePackageMap;
    }

    /**
     * @return FinalizedCmsDefinition
     */
    public function finalize()
    {
        return new FinalizedCmsDefinition($this->namePackageMap);
    }
}