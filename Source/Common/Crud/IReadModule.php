<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Module\IModule;
use Iddigital\Cms\Core\Module\IParameterizedAction;
use Iddigital\Cms\Core\Module\ITableDisplay;
use Iddigital\Cms\Core\Table\ITableDataSource;

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
     * @return ITableDisplay
     */
    public function getSummaryTable();

    /**
     * Returns whether the modules allows view in details.
     *
     * @return bool
     */
    public function allowsDetails();

    /**
     * Gets view object details action.
     *
     * @return IParameterizedAction
     * @throws InvalidOperationException
     */
    public function getDetailsAction();
}