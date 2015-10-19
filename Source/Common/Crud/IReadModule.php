<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Model\ITypedCollection;
use Iddigital\Cms\Core\Module\IModule;
use Iddigital\Cms\Core\Module\IParameterizedAction;
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
     * @return ITypedCollection
     */
    public function getSource();

    /**
     * @return ITableDataSource
     */
    public function getSummaryTable();

    /**
     * @return IParameterizedAction
     */
    public function getDetailsAction();
}