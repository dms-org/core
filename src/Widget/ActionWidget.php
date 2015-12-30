<?php

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
    public function getAction()
    {
        return $this->action;
    }
}