<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Form;
use Dms\Core\Persistence;

/**
 * The action handler interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IActionHandler
{
    /**
     * Gets the return type of data transfer object for this handler.
     *
     * @return string|null
     */
    public function getReturnTypeClass();
}