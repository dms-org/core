<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Module\IModuleItem;

/**
 * The widget interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IWidget extends IModuleItem
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
}