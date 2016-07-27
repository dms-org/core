<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;
use Dms\Core\Module\ModuleItem;

/**
 * The widget base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Widget extends ModuleItem implements IWidget
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
     * Widget constructor.
     *
     * @param string                      $name
     * @param string                      $label
     * @param IAuthSystemInPackageContext $authSystem
     * @param IPermission[]               $requiredPermissions
     */
    public function __construct(string $name, string $label, IAuthSystemInPackageContext $authSystem, array $requiredPermissions)
    {
        parent::__construct($authSystem, $requiredPermissions);

        $this->name  = $name;
        $this->label = $label;
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
    final public function isAuthorized() : bool
    {
        return parent::isAuthorized() && $this->hasExtraAuthorization();
    }

    abstract protected function hasExtraAuthorization() : bool;
}