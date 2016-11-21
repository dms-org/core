<?php declare(strict_types = 1);

namespace Dms\Core\Module\Action;

use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;
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
     * @var array
     */
    private $metadata;

    /**
     * Action constructor.
     *
     * @param string                      $name
     * @param IAuthSystemInPackageContext $auth
     * @param IPermission[]               $requiredPermissions
     * @param IActionHandler              $handler
     * @param array                       $metadata
     */
    public function __construct(string $name, IAuthSystemInPackageContext $auth, array $requiredPermissions, IActionHandler $handler, array $metadata)
    {
        parent::__construct($auth, $requiredPermissions);

        $this->name       = $name;
        $this->handler    = $handler;
        $this->returnType = $handler->getReturnTypeClass();
        $this->metadata   = $metadata;
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

    /**
     * @return array
     */
    final public function getMetadata(): array
    {
        return $this->metadata;
    }
}