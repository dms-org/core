<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Ioc\IIocContainer;

/**
 * The base auth system class.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class AuthSystem implements IAuthSystem
{
    /**
     * @inheritDoc
     */
    public function inPackageContext(IIocContainer $iocContainer, string $packageName) : IAuthSystemInPackageContext
    {
        return new AuthSystemInPackageContext($this, $packageName, $iocContainer);
    }
}