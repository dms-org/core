<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Module\IParameterizedAction;

/**
 * The object action is a parameterized action that
 * takes an object as a parameter.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IObjectAction extends IParameterizedAction
{
    const OBJECT_FIELD_NAME = 'object';

    /**
     * Gets the action handler
     *
     * @return IObjectActionHandler
     */
    public function getHandler();
}