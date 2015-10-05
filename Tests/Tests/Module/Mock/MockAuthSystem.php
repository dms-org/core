<?php

namespace Iddigital\Cms\Core\Tests\Module\Mock;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\InvalidCredentialsException;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\IUser;
use Iddigital\Cms\Core\Auth\UserBannedException;
use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Auth\UserNotAuthenticatedException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockAuthSystem implements IAuthSystem
{

    /**
     * Attempts to login with the supplied credentials.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     * @throws InvalidCredentialsException
     * @throws UserBannedException
     */
    public function login($username, $password)
    {

    }

    /**
     * Attempts to logout the currently authenticated user.
     *
     * @return void
     * @throws UserNotAuthenticatedException
     */
    public function logout()
    {

    }

    /**
     * Resets the users credentials.
     *
     * @param string $username
     * @param string $oldPassword
     * @param string $newPassword
     *
     * @return void
     * @throws InvalidCredentialsException
     * @throws UserBannedException
     */
    public function resetPassword($username, $oldPassword, $newPassword)
    {

    }

    /**
     * Returns whether there is an authenticated user.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return true;
    }

    /**
     * Returns the currently authenticated user.
     *
     * @return IUser
     * @throws UserNotAuthenticatedException
     */
    public function getAuthenticatedUser()
    {

    }

    /**
     * Returns whether the currently authenticated user has the
     * supplied permissions.
     *
     * @param IPermission[] $permissions
     *
     * @return boolean
     */
    public function isAuthorized(array $permissions)
    {
        return true;
    }

    /**
     * Verifies whether the currently authenticated user has the supplied
     * permissions.
     *
     * @param IPermission[] $permissions
     *
     * @return void
     * @throws UserForbiddenException
     * @throws UserNotAuthenticatedException
     * @throws UserBannedException
     */
    public function verifyAuthorized(array $permissions)
    {

    }
}