<?php declare(strict_types = 1);

namespace Dms\Core\Widget;
use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidOperationException;

/**
 * The widget interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IWidget
{
    /**
     * Gets the name of the parent package of this widget
     *
     * @return string|null
     */
    public function getPackageName();

    /**
     * Gets the name of the parent module of this widget
     *
     * @return string|null
     */
    public function getModuleName();


    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets the label.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Gets the permissions required to view this widget
     *
     * @return IPermission[]
     */
    public function getRequiredPermissions() : array;

    /**
     * Returns whether the current user authorized to see this widget.
     *
     * @return bool
     */
    public function isAuthorized() : bool;

    /**
     * Sets the name of the parent package module of this widget
     *
     * @param string $packageName
     * @param string $moduleName
     *
     * @return void
     * @throws InvalidOperationException if the names are already set
     */
    public function setPackageAndModuleName(string $packageName, string $moduleName);
}