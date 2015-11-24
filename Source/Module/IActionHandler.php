<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Persistence;

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