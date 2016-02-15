<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Module\IAction;

/**
 * The action widget class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionWidget extends Widget
{
    /**
     * @var IAction
     */
    protected $action;

    /**
     * @inheritDoc
     */
    public function __construct($name, $label, IAction $action)
    {
        parent::__construct($name, $label);

        $this->action = $action;
    }

    /**
     * @return IAction
     */
    public function getAction() : \Dms\Core\Module\IAction
    {
        return $this->action;
    }

    /**
     * Returns whether the current user authorized to see this widget.
     *
     * @return bool
     */
    public function isAuthorized() : bool
    {
        return $this->action->isAuthorized();
    }
}