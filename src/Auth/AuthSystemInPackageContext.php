<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Event\IEventDispatcher;
use Dms\Core\Ioc\IIocContainer;

/**
 * The auth system in a package context decorator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AuthSystemInPackageContext implements IAuthSystemInPackageContext
{
    /**
     * @var IAuthSystem
     */
    protected $innerAuthSystem;

    /**
     * @var string
     */
    protected $packageName;

    /**
     * @var IIocContainer
     */
    protected $iocContainer;

    /**
     * AuthSystemInPackageContext constructor.
     *
     * @param IAuthSystem   $innerAuthSystem
     * @param string        $packageName
     * @param IIocContainer $iocContainer
     */
    public function __construct(IAuthSystem $innerAuthSystem, string $packageName, IIocContainer $iocContainer)
    {
        $this->innerAuthSystem = $innerAuthSystem;
        $this->packageName     = $packageName;
        $this->iocContainer    = $iocContainer;
    }

    /**
     * @inheritDoc
     */
    public function login(string $username, string $password)
    {
        $this->innerAuthSystem->login($username, $password);
    }

    /**
     * @inheritDoc
     */
    public function logout()
    {
        $this->innerAuthSystem->logout();
    }

    /**
     * @inheritDoc
     */
    public function resetPassword(string $username, string $oldPassword, string $newPassword)
    {
        $this->innerAuthSystem->resetPassword($username, $oldPassword, $newPassword);
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated() : bool
    {
        return $this->innerAuthSystem->isAuthenticated();
    }

    /**
     * @inheritDoc
     */
    public function getAuthenticatedUser() : IAdmin
    {
        return $this->innerAuthSystem->getAuthenticatedUser();
    }

    /**
     * @inheritDoc
     */
    public function isAuthorized(array $permissions) : bool
    {
        return $this->innerAuthSystem->isAuthorized($permissions);
    }

    /**
     * @inheritDoc
     */
    public function verifyAuthorized(array $permissions)
    {
        $this->innerAuthSystem->verifyAuthorized($permissions);
    }

    /**
     * @inheritDoc
     */
    public function inPackageContext(IIocContainer $iocContainer, string $packageName) : IAuthSystemInPackageContext
    {
        return new self($this->innerAuthSystem, $packageName, $iocContainer);
    }

    /**
     * @inheritDoc
     */
    public function getPackageName() : string
    {
        return $this->packageName;
    }

    /**
     * @inheritDoc
     */
    public function getIocContainer() : IIocContainer
    {
        return $this->iocContainer;
    }

    /**
     * @inheritDoc
     */
    public function getEventDispatcher() : IEventDispatcher
    {
        return $this->iocContainer->get(IEventDispatcher::class);
    }
}