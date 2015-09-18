<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Persistence;

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
    public function getDtoType();

    /**
     * Runs the action handler.
     *
     * @param IDataTransferObject $data
     *
     * @returns IDataTransferObject|null
     * @throws TypeMismatchException
     */
    public function run(IDataTransferObject $data);
}