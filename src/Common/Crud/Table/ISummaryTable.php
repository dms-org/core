<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Table;

use Dms\Core\Common\Crud\Action\Table\IReorderAction;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\ITableDisplay;

/**
 * The summary table interface.
 *
 * This extends the standard table display to support reordering actions.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ISummaryTable extends ITableDisplay
{
    /**
     * Gets the defined reorder actions.
     *
     * @return IReorderAction[]
     */
    public function getReorderActions() : array;

    /**
     * Returns whether the supplied view has a reorder action.
     *
     * @param string $viewName
     *
     * @return bool
     */
    public function hasReorderAction(string $viewName) : bool;

    /**
     * Gets the reorder action for the supplied view.
     *
     * @param string $viewName
     *
     * @return IReorderAction
     * @throws InvalidArgumentException
     */
    public function getReorderAction(string $viewName) : IReorderAction;
}