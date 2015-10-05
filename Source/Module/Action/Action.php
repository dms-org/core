<?php

namespace Iddigital\Cms\Core\Module\Action;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Module\IActionHandler;

/**
 * The action base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class Action implements IAction
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var IAuthSystem
     */
    private $auth;

    /**
     * @var IPermission[]
     */
    private $requiredPermissions;

    /**
     * @var IActionHandler
     */
    private $handler;

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
    public function __construct($name, IAuthSystem $auth, array $requiredPermissions, IActionHandler $handler)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'requiredPermissions', $requiredPermissions, IPermission::class);

        $this->name                = $name;
        $this->auth                = $auth;
        $this->requiredPermissions = $requiredPermissions;
        $this->handler             = $handler;
        $this->returnType          = $handler->getReturnDtoType();
    }

    /**
     * {@inheritdoc}
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    final public function getRequiredPermissions()
    {
        return $this->requiredPermissions;
    }

    /**
     * {@inheritdoc}
     */
    final public function isAuthorized()
    {
        return $this->auth->isAuthorized($this->requiredPermissions);
    }

    /**
     * @return IActionHandler
     */
    final public function getHandler()
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     */
    final public function hasReturnDtoType()
    {
        return $this->returnType !== null;
    }

    /**
     * {@inheritdoc}
     */
    final public function getReturnDtoType()
    {
        return $this->returnType;
    }

    /**
     * Verifies the currently authenticated user has permission to perform
     * this action.
     *
     * @return void
     * @throws UserForbiddenException
     */
    final protected function verifyUserHasPermission()
    {
        $this->auth->verifyAuthorized($this->requiredPermissions);
    }
}