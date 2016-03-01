<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Module\ActionNotFoundException;
use Dms\Core\Module\IModule;
use Dms\Core\Module\IUnparameterizedAction;

/**
 * The interface for a read module.
 *
 * This provides a set of read actions and displays regarding
 * a repository or object set.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IReadModule extends IModule
{
    const SUMMARY_TABLE = 'summary-table';
    const SUMMARY_TABLE_ID_COLUMN = 'id';

    const VIEW_PERMISSION = 'view';

    const SUMMARY_TABLE_ACTION = 'summary-table';
    const DETAILS_ACTION = 'details';

    /**
     * Gets the type of objects contained within the objects.
     *
     * @return string
     */
    public function getObjectType() : string;

    /**
     * Gets the underlying object data source.
     *
     * @return IObjectSet
     */
    public function getDataSource() : IObjectSet;

    /**
     * Gets a label string for the supplied typed object.
     *
     * @param ITypedObject $object
     *
     * @return string
     */
    public function getLabelFor(ITypedObject $object) : string;

    /**
     * Gets the table display for the summary table.
     *
     * @return ISummaryTable
     */
    public function getSummaryTable() : ISummaryTable;

    /**
     * Gets the defined object actions.
     *
     * @return IObjectAction[]
     */
    public function getObjectActions() : array;

    /**
     * Returns whether an object action with the supplied name is defined
     * in this module.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasObjectAction(string $name) : bool;

    /**
     * Gets the object action with the supplied name.
     *
     * @param string $name
     *
     * @return IObjectAction
     * @throws ActionNotFoundException
     */
    public function getObjectAction(string $name) : IObjectAction;

    /**
     * Gets the action to view the summary table.
     *
     * @return IUnparameterizedAction
     */
    public function getSummaryTableAction() : IUnparameterizedAction;

    /**
     * Returns whether the modules allows view in details.
     *
     * @return bool
     */
    public function allowsDetails() : bool;

    /**
     * Gets view object details action.
     *
     * @return IObjectAction
     * @throws UnsupportedActionException
     */
    public function getDetailsAction() : IObjectAction;
}