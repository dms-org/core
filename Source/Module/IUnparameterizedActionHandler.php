<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Persistence;

/**
 * The unparamterized action handler interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IUnparameterizedActionHandler extends IActionHandler
{
    /**
     * Runs the action handler.
     *
     * @returns IDataTransferObject|null
     * @throws TypeMismatchException
     */
    public function run();
}