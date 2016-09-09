<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\IEntity;

/**
 * The admin interface.
 *
 * Represents a user account for the cms.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IAdmin extends IEntity
{
    /**
     * Gets the user's email address.
     *
     * @return string
     */
    public function getEmailAddress() : string;

    /**
     * Gets the username.
     *
     * @return string
     */
    public function getUsername() : string;

    /**
     * Returns whether the user is a super user.
     *
     * @return boolean
     */
    public function isSuperUser() : bool;

    /**
     * Returns whether the user is banned.
     *
     * @return boolean
     */
    public function isBanned() : bool;

    /**
     * Gets the user's role ids.
     *
     * @return EntityIdCollection
     */
    public function getRoleIds() : EntityIdCollection;
}
