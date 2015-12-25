<?php

namespace Dms\Core\Auth;

use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\IEntity;

interface IUser extends IEntity
{
    /**
     * Gets the user's email address.
     *
     * @return string
     */
    public function getEmailAddress();

    /**
     * Gets the username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Gets the user's hashed password.
     *
     * @return IHashedPassword
     */
    public function getPassword();

    /**
     * Sets the user's hashed password.
     *
     * @param IHashedPassword $password
     *
     * @return void
     */
    public function setPassword(IHashedPassword $password);

    /**
     * Returns whether the user is a super user.
     *
     * @return boolean
     */
    public function isSuperUser();

    /**
     * Returns whether the user is banned.
     *
     * @return boolean
     */
    public function isBanned();

    /**
     * Gets the user's role ids.
     *
     * @return EntityIdCollection
     */
    public function getRoleIds();
}
