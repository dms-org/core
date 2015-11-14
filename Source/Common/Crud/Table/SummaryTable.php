<?php

namespace Iddigital\Cms\Core\Common\Crud\Table;

use Iddigital\Cms\Core\Common\Crud\Action\Table\IReorderAction;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\ITableView;
use Iddigital\Cms\Core\Module\Table\TableDisplay;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The summary table class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SummaryTable extends TableDisplay implements ISummaryTable
{
    /**
     * @var IReorderAction[]
     */
    protected $reorderActions;

    /**
     * @param string           $name
     * @param ITableDataSource $dataSource
     * @param ITableView[]     $views
     * @param IReorderAction[] $viewNameReorderActionMap
     */
    public function __construct($name, ITableDataSource $dataSource, array $views, array $viewNameReorderActionMap = [])
    {
        parent::__construct($name, $dataSource, $views);

        InvalidArgumentException::verifyAllInstanceOf(
                __METHOD__,
                'viewNameReorderActionMap',
                $viewNameReorderActionMap,
                IReorderAction::class
        );

        foreach ($viewNameReorderActionMap as $viewName => $reorderAction) {
            $this->getView($viewName);
        }

        $this->reorderActions = $viewNameReorderActionMap;
    }

    /**
     * @inheritdoc
     */
    public function getReorderActions()
    {
        return $this->reorderActions;
    }

    /**
     * @inheritdoc
     */
    public function hasReorderAction($viewName)
    {
        return isset($this->reorderActions[$viewName]);
    }

    /**
     * @inheritdoc
     */
    public function getReorderAction($viewName)
    {
        if (!isset($this->reorderActions[$viewName])) {
            throw InvalidArgumentException::format(
                    'Invalid view name supplied to %s: expecting one of (%s), \'%s\' given',
                    __METHOD__, Debug::getType(array_keys($this->reorderActions)), $viewName
            );
        }

        return $this->reorderActions[$viewName];
    }
}