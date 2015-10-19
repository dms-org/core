<?php

namespace Iddigital\Cms\Core\Module\Table;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\ITableDisplay;
use Iddigital\Cms\Core\Module\ITableView;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The table display class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableDisplay implements ITableDisplay
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ITableDataSource
     */
    protected $dataSource;

    /**
     * @var ITableView[]
     */
    protected $views = [];

    /**
     * TableDisplay constructor.
     *
     * @param string           $name
     * @param ITableDataSource $dataSource
     * @param ITableView[]     $views
     */
    public function __construct($name, ITableDataSource $dataSource, array $views)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'views', $views, ITableView::class);

        $this->name       = $name;
        $this->dataSource = $dataSource;

        foreach ($views as $view) {
            $this->views[$view->getName()] = $view;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ITableDataSource
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @return ITableView|null
     */
    public function getDefaultView()
    {
        foreach ($this->views as $view) {
            if ($view->isDefault()) {
                return $view;
            }
        }

        return reset($this->views) ?: TableView::createDefault();
    }

    /**
     * @return ITableView[]
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasView($name)
    {
        return isset($this->views[$name]);
    }

    /**
     * @param string $name
     *
     * @return ITableView
     * @throws InvalidArgumentException
     */
    public function getView($name)
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: invalid view name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->views)), $name
        );
    }
}