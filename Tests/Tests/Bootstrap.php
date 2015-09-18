<?php

namespace Iddigital\Cms\Core\Tests;

use Composer\Autoload\ClassLoader;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Bootstrap
{
    /**
     * @var ClassLoader
     */
    private static $composer;

    /**
     * @var string
     */
    private static $vendorPath;

    /**
     * @param ClassLoader $composer
     * @param string      $vendorPath
     *
     * @return void
     */
    public static function load(ClassLoader $composer, $vendorPath)
    {
        self::$composer   = $composer;
        self::$vendorPath = $vendorPath;

        CustomComparators::load();
    }

    /**
     * @return ClassLoader
     */
    public static function getComposer()
    {
        return self::$composer;
    }

    /**
     * @return string
     */
    public static function getVendorPath()
    {
        return self::$vendorPath;
    }
}