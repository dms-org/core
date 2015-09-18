<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Module\IModule;
use Iddigital\Cms\Core\Module\IParameterizedAction;

/**
 * The interface for a CRUD context.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ICrudModule extends IModule
{
    /**
     * @return IParameterizedAction
     */
    public function getCreateAction();

    /**
     * @return IParameterizedAction
     */
    public function getEditAction();

    /**
     * @return IParameterizedAction
     */
    public function getRemoveAction();
}