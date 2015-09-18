<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Persistence;

/**
 * The action interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IAction
{
    /**
     * Returns whether the action has a return type.
     *
     * @return bool
     */
    public function hasReturnDtoType();

    /**
     * Gets the return type of data transfer object for this handler.
     *
     * @return string|null
     */
    public function getReturnDtoType();

    /**
     * Gets the permissions required to execute the action.
     *
     * @return IPermission[]
     */
    public function getRequiredPermissions();

    /**
     * Returns whether the currently authenticated user is authorized.
     *
     * @return bool
     */
    public function isAuthorized();

    /**
     * Gets the action handler
     *
     * @return IActionHandler
     */
    public function getHandler();
}