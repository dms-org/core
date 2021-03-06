<?php declare(strict_types = 1);

namespace Dms\Core;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Event\IEventDispatcher;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Ioc\IIocContainer;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Package\IPackage;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Core\Util\Debug;
use Psr\Cache\CacheItemPoolInterface;

/**
 * The cms base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Cms implements ICms
{
    /**
     * @var IIocContainer
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
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var IEventDispatcher
     */
    protected $eventDispatcher;

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
     * @param IIocContainer $container
     */
    public function __construct(IIocContainer $container)
    {
        $this->container       = $container;
        $this->lang            = $container->get(ILanguageProvider::class);
        $this->cache           = $container->get(CacheItemPoolInterface::class);
        $this->eventDispatcher = $container->get(IEventDispatcher::class);

        $definition = new CmsDefinition();
        $this->define($definition);
        $finalizedDefinition = $definition->finalize();

        $this->namePackageClassMap = $finalizedDefinition->getNamePackageMap();

        $this->bootPackages();

        $this->auth = $container->get(IAuthSystem::class);
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
     * @return void
     */
    protected function bootPackages()
    {
        foreach ($this->namePackageClassMap as $packageName => $packageClass) {
            if (is_callable([$packageClass, 'boot'])) {
                $packageClass::boot($this);
                $this->eventDispatcher->emit($packageName . '.boot');
            }
        }
    }

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

            $this->eventDispatcher->emit($name . '.load');
            $this->loadedPackages[$name] = $this->loadPackageFromClass($name, $packageClass);
            $this->eventDispatcher->emit($name . '.loaded', $this->loadedPackages[$name]);
        }

        return $this->loadedPackages[$name];
    }

    /**
     * @param string          $name
     * @param string|callable $packageClass
     *
     * @return IPackage
     * @throws InvalidArgumentException
     */
    private function loadPackageFromClass(string $name, $packageClass) : Package\IPackage
    {
        if (is_callable($packageClass)) {
            $package      = $packageClass($this, $name);
            $packageClass = is_scalar($package) ? gettype($package) : get_class($package);
        } else {
            $package = null;
        }

        if (!is_subclass_of($packageClass, IPackage::class, true)) {
            throw InvalidArgumentException::format(
                'Invalid package class defined within cms: expecting subclass of %s, %s given',
                IPackage::class, $packageClass
            );
        }

        /** @var IPackage $package */
        if (!$package) {
            $package = $this->container->get($packageClass);
        }

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
    final public function getCache() : CacheItemPoolInterface
    {
        return $this->cache;
    }

    /**
     * @inheritDoc
     */
    final public function getEventDispatcher() : IEventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @inheritDoc
     */
    final public function getIocContainer() : IIocContainer
    {
        return $this->container;
    }
}