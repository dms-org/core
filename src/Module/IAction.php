<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form;
use Dms\Core\Persistence;

/**
 * The action interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IAction
{
    /**
     * Gets the name of the action.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Returns whether the action has a return type.
     *
     * @return bool
     */
    public function hasReturnType() : bool;

    /**
     * Gets the return type of data transfer object for this handler.
     *
     * @return string|null
     */
    public function getReturnTypeClass();

    /**
     * Gets the name of the parent package of this action
     *
     * @return string|null
     */
    public function getPackageName();

    /**
     * Gets the name of the parent module of this action
     *
     * @return string|null
     */
    public function getModuleName();

    /**
     * Sets the name of the parent package module of this action
     *
     * @param string $packageName
     * @param string $moduleName
     *
     * @return void
     * @throws InvalidOperationException if the names are already set
     */
    public function setPackageAndModuleName(string $packageName, string $moduleName);

    /**
     * Gets the permissions required to execute the action.
     *
     * @return IPermission[]
     */
    public function getRequiredPermissions() : array;

    /**
     * Returns whether the action requires a permission with the supplied name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function requiresPermission(string $name) : bool;

    /**
     * Gets required permission with the supplied name.
     *
     * @param string $name
     *
     * @return IPermission
     * @throws InvalidArgumentException
     */
    public function getRequiredPermission(string $name) : IPermission;

    /**
     * Returns whether the currently authenticated user is authorized.
     *
     * @return bool
     */
    public function isAuthorized() : bool;

    /**
     * Gets the action handler
     *
     * @return IActionHandler
     */
    public function getHandler() : IActionHandler;
}