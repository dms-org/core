<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Table;

use Dms\Core\Common\Crud\Action\Table\IReorderAction;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\ITableView;
use Dms\Core\Module\Table\TableDisplay;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Util\Debug;

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
    public function __construct(string $name, ITableDataSource $dataSource, array $views, array $viewNameReorderActionMap = [])
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
    public function getReorderActions() : array
    {
        return $this->reorderActions;
    }

    /**
     * @inheritdoc
     */
    public function hasReorderAction(string $viewName) : bool
    {
        return isset($this->reorderActions[$viewName]);
    }

    /**
     * @inheritdoc
     */
    public function getReorderAction(string $viewName) : IReorderAction
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