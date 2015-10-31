<?php

namespace Iddigital\Cms\Core\Common\Crud\Action;

use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;

/**
 * The object action is a parameterized action that
 * takes an object as a parameter.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IObjectActionHandler extends IParameterizedActionHandler
{
    /**
     * Runs the action on the supplied object.
     *
     * @param object                   $object
     * @param IDataTransferObject|null $data
     */
    public function runOnObject($object, IDataTransferObject $data = null);
}