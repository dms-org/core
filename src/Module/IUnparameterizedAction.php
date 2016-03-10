<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Auth\AdminForbiddenException;
use Dms\Core\Form;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Persistence;

/**
 * The unparameterized action interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IUnparameterizedAction extends IAction
{
    /**
     * Gets the action handler
     *
     * @return IUnparameterizedActionHandler
     */
    public function getHandler() : IActionHandler;

    /**
     * Runs the action handler.
     *
     * @return IDataTransferObject|null
     * @throws AdminForbiddenException
     */
    public function run();

    /**
     * Runs the action without verifying the user is authorized.
     *
     * @return object|null
     */
    public function runWithoutAuthorization();
}