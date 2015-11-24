<?php

namespace Iddigital\Cms\Core\Module\Action;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Module\IActionHandler;
use Iddigital\Cms\Core\Util\Debug;

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
    private $requiredPermissions = [];

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
    public function __construct($name, IAuthSystem $auth, array $requiredPermissions, IActionHandler $handler)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'requiredPermissions', $requiredPermissions, IPermission::class);

        $this->name                = $name;
        $this->auth                = $auth;
        $this->handler             = $handler;
        $this->returnType          = $handler->getReturnTypeClass();

        foreach ($requiredPermissions as $requiredPermission) {
            $this->requiredPermissions[$requiredPermission->getName()] = $requiredPermission;
        }
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
    public function requiresPermission($name)
    {
        return isset($this->requiredPermissions[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPermission($name)
    {
        if (!isset($this->requiredPermissions[$name])) {
            throw InvalidArgumentException::format(
                    'Invalid permission name supplied to %s: expecting one of (%s), \'%s\' given',
                    __METHOD__, Debug::formatValues(array_keys($this->requiredPermissions)), $name
            );
        }

        return $this->requiredPermissions[$name];
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
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     */
    final public function hasReturnType()
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