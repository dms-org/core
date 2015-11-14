<?php

namespace Iddigital\Cms\Core\Common\Crud\Table;

use Iddigital\Cms\Core\Common\Crud\Action\Table\IReorderAction;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\ITableDisplay;

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
    public function getReorderActions();

    /**
     * Returns whether the supplied view has a reorder action.
     *
     * @param string $viewName
     *
     * @return bool
     */
    public function hasReorderAction($viewName);

    /**
     * Gets the reorder action for the supplied view.
     *
     * @param string $viewName
     *
     * @return IReorderAction
     * @throws InvalidArgumentException
     */
    public function getReorderAction($viewName);
}