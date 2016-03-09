<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidArgumentException;

/**
 * The widget base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Widget implements IWidget
{
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
     * @inheritdoc
     */
    final public function isAuthorized() : bool
    {
        return $this->authSystem->isAuthorized($this->requiredPermissions)
        && $this->hasExtraAuthorization();
    }

    abstract protected function hasExtraAuthorization() : bool;
}