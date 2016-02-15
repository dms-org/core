<?php

namespace Dms\Core\Tests\Module\Mock;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\InvalidCredentialsException;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\IUser;
use Dms\Core\Auth\UserBannedException;
use Dms\Core\Auth\UserForbiddenException;
use Dms\Core\Auth\UserNotAuthenticatedException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockAuthSystem implements IAuthSystem
{
    /**
     * @var IUser
     */
    protected $mockUser;

    /**
     * @var bool
     */
    protected $authorized = true;

    /**
     * MockAuthSystem constructor.
     *
     * @param IUser $mockUser
     */
    public function __construct(IUser $mockUser)
    {
        $this->mockUser = $mockUser;
    }


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
    public function login(string $username, string $password)
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
    public function resetPassword(string $username, string $oldPassword, string $newPassword)
    {

    }

    /**
     * Returns whether there is an authenticated user.
     *
     * @return boolean
     */
    public function isAuthenticated() : bool
    {
        return true;
    }

    /**
     * Returns the currently authenticated user.
     *
     * @return IUser
     * @throws UserNotAuthenticatedException
     */
    public function getAuthenticatedUser() : IUser
    {
        return $this->mockUser;
    }

    /**
     * Returns whether the currently authenticated user has the
     * supplied permissions.
     *
     * @param IPermission[] $permissions
     *
     * @return boolean
     */
    public function isAuthorized(array $permissions) : bool
    {
        return $this->authorized;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setIsAuthorized(bool $flag)
    {
        $this->authorized = $flag;
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
        if (!$this->isAuthorized($permissions)) {
            throw new UserForbiddenException($this->mockUser, $permissions);
        }
    }
}