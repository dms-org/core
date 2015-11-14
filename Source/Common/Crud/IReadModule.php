<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Table\ISummaryTable;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Module\IModule;

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
    const DETAILS_ACTION = 'details';

    /**
     * Gets the type of objects contained within the objects.
     *
     * @return string
     */
    public function getObjectType();

    /**
     * Gets the underlying object source.
     *
     * @return IObjectSet
     */
    public function getObjectSource();

    /**
     * Gets the table display for the summary table.
     *
     * @return ISummaryTable
     */
    public function getSummaryTable();

    /**
     * Gets the defined object actions.
     *
     * @return IObjectAction[]
     */
    public function getObjectActions();

    /**
     * Returns whether an object action with the supplied name is defined
     * in this module.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasObjectAction($name);

    /**
     * Gets the object action with the supplied name.
     *
     * @param string $name
     *
     * @return IObjectAction
     * @throws InvalidArgumentException
     */
    public function getObjectAction($name);

    /**
     * Returns whether the modules allows view in details.
     *
     * @return bool
     */
    public function allowsDetails();

    /**
     * Gets view object details action.
     *
     * @return IObjectAction
     * @throws InvalidOperationException
     */
    public function getDetailsAction();
}