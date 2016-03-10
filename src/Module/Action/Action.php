<?php declare(strict_types = 1);

namespace Dms\Core\Module\Action;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Form;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IActionHandler;
use Dms\Core\Module\ModuleItem;

/**
 * The action base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class Action extends ModuleItem implements IAction
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var IActionHandler
     */
    protected $handler;

    /**
     * @var string|null
     */
    private $returnType;

    /**
     * Action constructor.
     *
     * @param string         $name
     * @param IAuthSystem    $auth
     * @param IPermission[]  $requiredPermissions
     * @param IActionHandler $handler
     */
    public function __construct(string $name, IAuthSystem $auth, array $requiredPermissions, IActionHandler $handler)
    {
        parent::__construct($auth, $requiredPermissions);

        $this->name       = $name;
        $this->handler    = $handler;
        $this->returnType = $handler->getReturnTypeClass();
    }

    /**
     * {@inheritdoc}
     */
    final public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return IActionHandler
     */
    public function getHandler() : IActionHandler
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     */
    final public function hasReturnType() : bool
    {
        return $this->returnType !== null;
    }

    /**
     * {@inheritdoc}
     */
    final public function getReturnTypeClass()
    {
        return $this->returnType;
    }
}