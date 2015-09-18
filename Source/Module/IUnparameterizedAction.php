<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Persistence;

/**
 * The unparameterized action interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IUnparameterizedAction
{
    /**
     * Gets the action handler
     *
     * @return IUnparameterizedActionHandler
     */
    public function getHandler();

    /**
     * Runs the action handler.
     *
     * @return IDataTransferObject|null
     * @throws UserForbiddenException
     */
    public function run();
}