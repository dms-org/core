<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Model\IDataTransferObject;
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

    /**
     * Runs the action on the supplied object.
     *
     * @param object $object
     * @param array  $data
     *
     * @return IDataTransferObject|null
     * @throws UserForbiddenException if the authenticated user does not have the required permissions
     * @throws InvalidFormSubmissionException if the form data is invalid
     */
    public function runOnObject($object, array $data);
}