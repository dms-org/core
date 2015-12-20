<?php

namespace Dms;

use Composer\Autoload\ClassLoader;
use Dms\Common\Testing;
use Dms\Core\Tests;

$projectAutoLoaderPath = __DIR__ . '/../vendor/autoload.php';
$dependencyAutoLoaderPath = __DIR__ . '/../../../../autoload.php';

if (file_exists($projectAutoLoaderPath)) {
    $vendorPath = realpath(__DIR__ . '/../vendor/');
    $composerAutoLoader = require $projectAutoLoaderPath;
} elseif (file_exists($dependencyAutoLoaderPath)) {
    $vendorPath = realpath(__DIR__ . '/../../../../');
    $composerAutoLoader = require $dependencyAutoLoaderPath;
} else {
    throw new \Exception('Cannot load tests for '. __NAMESPACE__ . ' under ' . __DIR__. ': please load via composer');
}

/** @var ClassLoader $composerAutoLoader */
$composerAutoLoader->addPsr4(__NAMESPACE__ . '\\', __DIR__);

Tests\Bootstrap::load($composerAutoLoader, $vendorPath);
Testing\Bootstrapper::run(__NAMESPACE__, __DIR__, 'phpunit.xml');