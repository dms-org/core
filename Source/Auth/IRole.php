<?php

namespace Iddigital\Cms\Core\Auth;

use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\ValueObjectCollection;

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
