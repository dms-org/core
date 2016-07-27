<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IAuthSystemInPackageContext;
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
    public function __construct(string $name, string $label, IAuthSystemInPackageContext $authSystem, array $requiredPermissions, IAction $action)
    {
        parent::__construct($name, $label, $authSystem, $requiredPermissions);

        $this->action = $action;
    }

    /**
     * @return IAction
     */
    public function getAction() : IAction
    {
        return $this->action;
    }

    protected function hasExtraAuthorization() : bool
    {
        return $this->action->isAuthorized();
    }
}