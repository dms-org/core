<?php declare(strict_types = 1);

namespace Dms\Core;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Package\IPackage;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Core\Util\Debug;
use Interop\Container\ContainerInterface;

/**
 * The cms base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Cms implements ICms
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var IAuthSystem
     */
    protected $auth;

    /**
     * @var ILanguageProvider
     */
    protected $lang;

    /**
     * @var string[]
     */
    protected $namePackageClassMap;

    /**
     * @var IPackage[]
     */
    protected $loadedPackages = [];

    /**
     * Cms constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->auth = $container->get(IAuthSystem::class);
        $this->lang = $container->get(ILanguageProvider::class);

        $definition = new CmsDefinition();
        $this->define($definition);
        $finalizedDefinition = $definition->finalize();

        $this->namePackageClassMap = $finalizedDefinition->getNamePackageMap();
    }

    /**
     * Defines the structure and installed packages of the cms.
     *
     * @param CmsDefinition $cms
     *
     * @return void
     */
    abstract protected function define(CmsDefinition $cms);

    /**
     * @inheritDoc
     */
    final public function getPackageNames() : array
    {
        return array_keys($this->namePackageClassMap);
    }

    /**
     * @inheritDoc
     */
    final public function loadPackages() : array
    {
        $packages = [];

        foreach ($this->namePackageClassMap as $packageName => $packageClass) {
            $packages[$packageName] = $this->loadPackage($packageName);
        }

        return $packages;
    }

    /**
     * @inheritDoc
     */
    final public function hasPackage(string $name) : bool
    {
        return isset($this->namePackageClassMap[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function loadPackage(string $name) : Package\IPackage
    {
        if (!isset($this->namePackageClassMap[$name])) {
            throw PackageNotFoundException::format(
                    'Invalid package name supplied to %s: expecting one of (%s), \'%s\' given',
                    get_class($this) . '::' . __FUNCTION__, Debug::formatValues($this->getPackageNames()), $name
            );
        }

        if (!isset($this->loadedPackages[$name])) {
            $packageClass = $this->namePackageClassMap[$name];

            $this->loadedPackages[$name] = $this->loadPackageFromClass($name, $packageClass);
        }

        return $this->loadedPackages[$name];
    }

    /**
     * @param string $name
     * @param string $packageClass
     *
     * @return IPackage
     * @throws InvalidArgumentException
     */
    private function loadPackageFromClass(string $name, string $packageClass) : Package\IPackage
    {
        if (!is_subclass_of($packageClass, IPackage::class, true)) {
            throw InvalidArgumentException::format(
                    'Invalid package class defined within cms: expecting subclass of %s, %s given',
                    IPackage::class, $packageClass
            );
        }

        /** @var IPackage $package */
        $package = $this->container->get($packageClass);

        if ($package->getName() !== $name) {
            throw InvalidArgumentException::format(
                    'Invalid package class defined within package: defined package name \'%s\' does not match package name \'%s\' from instance of %s',
                    $name, $package->getName(), get_class($package)
            );
        }

        return $package;
    }

    /**
     * @inheritDoc
     */
    public function loadPermissions() : array
    {
        $namespacedPermissions = [];

        foreach ($this->loadPackages() as $packageName => $package) {
            foreach ($package->loadPermissions() as $permission) {
                $namespacedPermissions[] = $permission;
            }
        }

        return $namespacedPermissions;
    }

    /**
     * @inheritDoc
     */
    final public function getAuth() : Auth\IAuthSystem
    {
        return $this->auth;
    }

    /**
     * @inheritDoc
     */
    final public function getLang() : Language\ILanguageProvider
    {
        return $this->lang;
    }

    /**
     * @inheritDoc
     */
    final public function getIocContainer() : \Interop\Container\ContainerInterface
    {
        return $this->container;
    }
}