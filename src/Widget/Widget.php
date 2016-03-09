<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;

/**
 * The widget base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Widget implements IWidget
{
    /**
     * @var string|null
     */
    protected $packageName;

    /**
     * @var string|null
     */
    protected $moduleName;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var IAuthSystem
     */
    protected $authSystem;

    /**
     * @var array
     */
    protected $requiredPermissions;

    /**
     * Widget constructor.
     *
     * @param string        $name
     * @param string        $label
     * @param IAuthSystem   $authSystem
     * @param IPermission[] $requiredPermissions
     */
    public function __construct(string $name, string $label, IAuthSystem $authSystem, array $requiredPermissions)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'requiredPermissions', $requiredPermissions, IPermission::class);
        $this->name                = $name;
        $this->label               = $label;
        $this->requiredPermissions = $requiredPermissions;
        $this->authSystem          = $authSystem;
    }

    /**
     * @return string|null
     */
    final public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @return string|null
     */
    final public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * @inheritdoc
     */
    final public function getName() : string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    final public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    final public function getRequiredPermissions() : array
    {
        return $this->requiredPermissions;
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
     * @inheritdoc
     */
    final public function isAuthorized() : bool
    {
        return $this->authSystem->isAuthorized($this->requiredPermissions)
        && $this->hasExtraAuthorization();
    }

    abstract protected function hasExtraAuthorization() : bool;
}