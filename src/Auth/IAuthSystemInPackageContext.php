<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Event\IEventDispatcher;
use Dms\Core\Exception;
use Dms\Core\Ioc\IIocContainer;

/**
 * The authentication system interface in a package context.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IAuthSystemInPackageContext extends IAuthSystem
{
    /**
     * Gets the current package name.
     *
     * @return string
     */
    public function getPackageName() : string;

    /**
     * Gets the ioc container used by the auth system.
     *
     * @return IIocContainer
     */
    public function getIocContainer() : IIocContainer;

    /**
     * Gets the event dispatcher used by the auth system.
     *
     * @return IEventDispatcher
     */
    public function getEventDispatcher() : IEventDispatcher;
}
