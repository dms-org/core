<?php

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
    public function getName();

    /**
     * Gets the label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns whether the current user authorized to see this widget.
     *
     * @return bool
     */
    public function isAuthorized();
}