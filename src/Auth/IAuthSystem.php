<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Event\IEventDispatcher;
use Dms\Core\Exception;
use Dms\Core\Ioc\IIocContainer;

/**
 * The authentication system interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IAuthSystem
{
    /**
     * Attempts to login with the supplied credentials.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     * @throws InvalidCredentialsException
     * @throws AdminBannedException
     */
    public function login(string $username, string $password);

    /**
     * Attempts to logout the currently authenticated user.
     *
     * @return void
     * @throws NotAuthenticatedException
     */
    public function logout();

    /**
     * Resets the users credentials.
     *
     * @param string $username
     * @param string $oldPassword
     * @param string $newPassword
     *
     * @return void
     * @throws InvalidCredentialsException
     * @throws AdminBannedException
     */
    public function resetPassword(string $username, string $oldPassword, string $newPassword);

    /**
     * Returns whether there is an authenticated user.
     *
     * @return boolean
     */
    public function isAuthenticated() : bool;

    /**
     * Returns the currently authenticated user.
     *
     * @return IAdmin
     * @throws NotAuthenticatedException
     */
    public function getAuthenticatedUser() : IAdmin;

    /**
     * Returns whether the currently authenticated user has the
     * supplied permissions.
     *
     * @param IPermission[] $permissions
     *
     * @return boolean
     */
    public function isAuthorized(array $permissions) : bool;

    /**
     * Verifies whether the currently authenticated user has the supplied
     * permissions.
     *
     * @param IPermission[] $permissions
     *
     * @return void
     * @throws AdminForbiddenException
     * @throws NotAuthenticatedException
     * @throws AdminBannedException
     */
    public function verifyAuthorized(array $permissions);

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
