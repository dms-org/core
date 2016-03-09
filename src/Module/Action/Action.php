<?php declare(strict_types = 1);

namespace Dms\Core\Module\Action;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Auth\AdminForbiddenException;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IActionHandler;
use Dms\Core\Util\Debug;

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
     * @var string|null
     */
    private $moduleName;

    /**
     * @var string|null
     */
    private $packageName;

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
    public function __construct(string $name, IAuthSystem $auth, array $requiredPermissions, IActionHandler $handler)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'requiredPermissions', $requiredPermissions, IPermission::class);

        $this->name       = $name;
        $this->auth       = $auth;
        $this->handler    = $handler;
        $this->returnType = $handler->getReturnTypeClass();

        foreach ($requiredPermissions as $requiredPermission) {
            $this->requiredPermissions[$requiredPermission->getName()] = $requiredPermission;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function getName() : string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * {@inheritdoc}
     */
    public function setPackageAndModuleName(string $packageName, string $moduleName)
    {
        if ($this->packageName || $this->moduleName) {
            throw InvalidOperationException::methodCall(__METHOD__, 'package/module name already set');
        }

        $this->packageName         = $packageName;
        $this->moduleName          = $moduleName;
        $this->requiredPermissions = Permission::namespaceAll($this->requiredPermissions, $packageName . '.' . $moduleName);
    }

    /**
     * {@inheritdoc}
     */
    final public function getRequiredPermissions() : array
    {
        return $this->requiredPermissions;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresPermission(string $name) : bool
    {
        return isset($this->requiredPermissions[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPermission(string $name) : \Dms\Core\Auth\IPermission
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
    final public function isAuthorized() : bool
    {
        return $this->auth->isAuthorized($this->requiredPermissions);
    }

    /**
     * @return IActionHandler
     */
    public function getHandler() : \Dms\Core\Module\IActionHandler
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
     * Verifies the currently authenticated user has permission to perform
     * this action.
     *
     * @return void
     * @throws AdminForbiddenException
     */
    final protected function verifyUserHasPermission()
    {
        $this->auth->verifyAuthorized($this->requiredPermissions);
    }
}