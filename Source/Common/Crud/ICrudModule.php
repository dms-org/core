<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Module\IParameterizedAction;

/**
 * The interface for a CRUD module.
 *
 * This provides a set of CRUD actions and displays regarding
 * a set of objects.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ICrudModule extends IReadModule
{
    /**
     * Returns whether the modules allows objects to be created.
     *
     * @return bool
     */
    public function allowsCreate();

    /**
     * Gets the create object action.
     *
     * @return IParameterizedAction
     * @throws InvalidOperationException
     */
    public function getCreateAction();

    /**
     * Returns whether the modules allows objects to be edited.
     *
     * @return bool
     */
    public function allowsEdit();

    /**
     * Gets the edit object action.
     *
     * @return IParameterizedAction
     * @throws InvalidOperationException
     */
    public function getEditAction();

    /**
     * Returns whether the modules allows objects to be removed.
     *
     * @return bool
     */
    public function allowsRemove();

    /**
     * Gets the remove object action.
     *
     * @return IParameterizedAction
     * @throws InvalidOperationException
     */
    public function getRemoveAction();
}