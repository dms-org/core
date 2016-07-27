<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Auth\AdminForbiddenException;
use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form;
use Dms\Core\Util\Debug;

/**
 * The module item base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class ModuleItem implements IModuleItem
{
    /**
     * @var string|null
     */
    protected $moduleName;

    /**
     * @var string|null
     */
    protected $packageName;

    /**
     * @var IAuthSystemInPackageContext
     */
    protected $auth;

    /**
     * @var IPermission[]
     */
    protected $requiredPermissions = [];

    /**
     * ModuleItem constructor.
     *
     * @param IAuthSystemInPackageContext $auth
     * @param IPermission[]               $requiredPermissions
     */
    public function __construct(IAuthSystemInPackageContext $auth, array $requiredPermissions)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'requiredPermissions', $requiredPermissions, IPermission::class);

        $this->auth = $auth;

        foreach ($requiredPermissions as $requiredPermission) {
            $this->requiredPermissions[$requiredPermission->getName()] = $requiredPermission;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * {@inheritdoc}
     */
    final public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * {@inheritdoc}
     */
    final public function setPackageAndModuleName(string $packageName, string $moduleName)
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
    final public function requiresPermission(string $name) : bool
    {
        return isset($this->requiredPermissions[$name]);
    }

    /**
     * {@inheritdoc}
     */
    final public function getRequiredPermission(string $name) : IPermission
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
    public function isAuthorized() : bool
    {
        return $this->auth->isAuthorized($this->requiredPermissions);
    }

    /**
     * @inheritDoc
     */
    final public function withoutRequiredPermissions()
    {
        $clone = clone $this;

        $clone->requiredPermissions = [];

        return $clone;
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