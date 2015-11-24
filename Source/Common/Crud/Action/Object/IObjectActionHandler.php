<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
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
     * Gets the expected type of object.
     *
     * @return string
     */
    public function getObjectType();

    /**
     * Returns whether the data dto parameter is required.
     *
     * @return bool
     */
    public function hasDataDtoType();

    /**
     * Gets the type if of the data dto parameter or NULL if no dto required.
     *
     * @return string|null
     */
    public function getDataDtoType();

    /**
     * Runs the action on the supplied object.
     *
     * @param object      $object
     * @param object|null $data
     *
     * @returns object|null
     * @throws TypeMismatchException
     */
    public function runOnObject($object, $data = null);
}