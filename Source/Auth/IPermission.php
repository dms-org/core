<?php

namespace Iddigital\Cms\Core\Auth;

use Iddigital\Cms\Core\Model\IValueObject;

interface IPermission extends IValueObject
{
    /**
     * Gets the permission name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns whether the permissions are equal
     *
     * @param IPermission $permission
     *
     * @return bool
     */
    public function equals(IPermission $permission);
}
