<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Module\IParameterizedAction;

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
    const CREATE_PERMISSION = 'create';
    const EDIT_PERMISSION = 'edit';
    const REMOVE_PERMISSION = 'remove';

    const CREATE_ACTION = 'create';
    const EDIT_ACTION = 'edit';
    const REMOVE_ACTION = 'remove';

    /**
     * Returns whether the modules allows objects to be created.
     *
     * @return bool
     */
    public function allowsCreate() : bool;

    /**
     * Gets the create object action.
     *
     * @return IParameterizedAction
     * @throws UnsupportedActionException
     */
    public function getCreateAction() : IParameterizedAction;

    /**
     * Returns whether the modules allows objects to be edited.
     *
     * @return bool
     */
    public function allowsEdit() : bool;

    /**
     * Gets the edit object action.
     *
     * @return IObjectAction
     * @throws UnsupportedActionException
     */
    public function getEditAction() : IObjectAction;

    /**
     * Returns whether the modules allows objects to be removed.
     *
     * @return bool
     */
    public function allowsRemove() : bool;

    /**
     * Gets the remove object action.
     *
     * @return IObjectAction
     * @throws UnsupportedActionException
     */
    public function getRemoveAction() : IObjectAction;
}