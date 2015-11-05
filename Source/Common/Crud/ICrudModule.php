<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Exception\InvalidOperationException;

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
    const CREATE_ACTION = 'create';
    const EDIT_ACTION = 'edit';
    const REMOVE_ACTION = 'remove';

    /**
     * Returns whether the modules allows objects to be created.
     *
     * @return bool
     */
    public function allowsCreate();

    /**
     * Gets the create object action.
     *
     * @return IObjectAction
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
     * @return IObjectAction
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
     * @return IObjectAction
     * @throws InvalidOperationException
     */
    public function getRemoveAction();
}