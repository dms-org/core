<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Persistence;

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