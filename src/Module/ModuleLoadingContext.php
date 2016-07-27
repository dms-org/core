<?php declare(strict_types = 1);

namespace Dms\Core\Module;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleLoadingContext
{
    /**
     * @var string
     */
    protected $packageName;

    /**
     * ModuleLoadingContext constructor.
     *
     * @param string $packageName
     */
    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    /**
     * @return string
     */
    public function getPackageName() : string 
    {
        return $this->packageName ?? '';
    }
}