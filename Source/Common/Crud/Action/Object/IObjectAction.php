<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\IForm;
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
     * Gets the action handler.
     *
     * @return IObjectActionHandler
     */
    public function getHandler();

    /**
     * Gets the expected type of object.
     *
     * @return string
     */
    public function getObjectType();

    /**
     * Gets the first stage of the action. This contains the object field.
     *
     * The form will be equivalent to the form defined in the object from class.
     * @see ObjectForm
     *
     * @return IForm
     */
    public function getObjectForm();

    /**
     * Returns an array containing the objects from the supplied array objects
     * of which are supported to be run with this object action.
     *
     * @param object[] $objects
     *
     * @return object[]
     * @throws TypeMismatchException if the array contains a value of the incorrect type.
     */
    public function getSupportedObjects(array $objects);

    /**
     * Returns whether the supplied object is supported in this action.
     *
     * @param object $object
     *
     * @return bool
     * @throws TypeMismatchException if the value is of the incorrect type.
     */
    public function isSupported($object);

    /**
     * Runs the action on the supplied object.
     *
     * @param object $object
     * @param array  $data
     *
     * @return object|null
     * @throws UserForbiddenException if the authenticated user does not have the required permissions
     * @throws InvalidFormSubmissionException if the form data is invalid
     */
    public function runOnObject($object, array $data);
}