<?php

namespace Dms\Core\Auth;

use Dms\Core\Model\IEntity;
use Dms\Core\Model\ValueObjectCollection;

interface IRole extends IEntity
{
    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the permission.
     *
     * @return ValueObjectCollection|IPermission[]
     */
    public function getPermissions();
}
