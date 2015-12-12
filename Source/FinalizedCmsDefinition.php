<?php

namespace Iddigital\Cms\Core;

/**
 * The finalized cms definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedCmsDefinition
{
    /**
     * @var string[]
     */
    private $namePackageMap;

    /**
     * FinalizedCmsDefinition constructor.
     *
     * @param string[] $namePackageMap
     */
    public function __construct(array $namePackageMap)
    {
        $this->namePackageMap = $namePackageMap;
    }

    /**
     * @return string[]
     */
    public function getNamePackageMap()
    {
        return $this->namePackageMap;
    }
}