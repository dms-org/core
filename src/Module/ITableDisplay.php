<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\IDataTable;
use Dms\Core\Table\ITableDataSource;

/**
 * The table display interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITableDisplay
{
    /**
     * Gets the name
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets the table data source
     *
     * @return ITableDataSource
     */
    public function getDataSource() : ITableDataSource;

    /**
     * Gets the default view.
     *
     * @return ITableView
     */
    public function getDefaultView() : ITableView;

    /**
     * Gets the views.
     *
     * @return ITableView[]
     */
    public function getViews() : array;

    /**
     * Get whether the view with the supplied name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasView(string $name) : bool;

    /**
     * Gets the view with the supplied name.
     *
     * @param string $name
     *
     * @return ITableView
     * @throws InvalidArgumentException
     */
    public function getView(string $name) : ITableView;

    /**
     * Loads the data the view with the supplied name.
     *
     * @param string|null $name null if default
     * @param int         $skipRows
     * @param int|null    $limitRows
     *
     * @return IDataTable
     */
    public function loadView(string $name = null, int $skipRows = 0, int $limitRows = null) : IDataTable;
}