<?php

namespace Dms\Core\Tests\Module\Mock;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\InvalidCredentialsException;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\IAdmin;
use Dms\Core\Auth\AdminBannedException;
use Dms\Core\Auth\AdminForbiddenException;
use Dms\Core\Auth\NotAuthenticatedException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockAuthSystem implements IAuthSystem
{
    /**
     * @var IAdmin
     */
    protected $mockUser;

    /**
     * @var bool
     */
    protected $authorized = true;

    /**
     * MockAuthSystem constructor.
     *
     * @param IAdmin $mockUser
     */
    public function __construct(IAdmin $mockUser)
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
     * @throws AdminBannedException
     */
    public function login(string $username, string $password)
    {

    }

    /**
     * Attempts to logout the currently authenticated user.
     *
     * @return void
     * @throws NotAuthenticatedException
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
     * @throws AdminBannedException
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
     * @return IAdmin
     * @throws NotAuthenticatedException
     */
    public function getAuthenticatedUser() : IAdmin
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
     * @throws AdminForbiddenException
     * @throws NotAuthenticatedException
     * @throws AdminBannedException
     */
    public function verifyAuthorized(array $permissions)
    {
        if (!$this->isAuthorized($permissions)) {
            throw new AdminForbiddenException($this->mockUser, $permissions);
        }
    }
}