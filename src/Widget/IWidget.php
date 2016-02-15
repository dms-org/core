<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

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
     * Returns whether the current user authorized to see this widget.
     *
     * @return bool
     */
    public function isAuthorized() : bool;
}