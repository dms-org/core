<?php declare(strict_types = 1);

namespace Dms\Core\Widget;
use Dms\Core\Auth\IPermission;

/**
 * The widget interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IWidget
{
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
}