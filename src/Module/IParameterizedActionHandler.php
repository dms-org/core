<?php

namespace Dms\Core\Module;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form;
use Dms\Core\Persistence;

/**
 * The parameterized action handler interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IParameterizedActionHandler extends IActionHandler
{
    /**
     * Gets the required type of data transfer object for this handler.
     *
     * @return string
     */
    public function getParameterTypeClass();

    /**
     * Runs the action handler.
     *
     * @param object $data
     *
     * @returns object|null
     * @throws TypeMismatchException
     */
    public function run($data);
}