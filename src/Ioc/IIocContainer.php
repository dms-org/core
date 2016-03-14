<?php declare(strict_types = 1);

namespace Dms\Core\Ioc;

use Interop\Container\ContainerInterface;

/**
 * The ioc container interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IIocContainer extends ContainerInterface
{
    const SCOPE_INSTANCE_PER_RESOLVE = 'instance-per-resolve';
    const SCOPE_SINGLETON = 'singleton';

    /**
     * Binds the supplied abstract class or interface to the supplied
     * concrete class name.
     *
     * @param string $scope
     * @param string $abstract
     * @param string $concrete
     *
     * @return void
     */
    public function bind(string $scope, string $abstract, string $concrete);

    /**
     * Binds the supplied abstract class or interface to the return value
     * of the supplied callback.
     *
     * @param string   $scope
     * @param string   $abstract
     * @param callable $factory
     *
     * @return void
     */
    public function bindCallback(string $scope, string $abstract, callable $factory);
}