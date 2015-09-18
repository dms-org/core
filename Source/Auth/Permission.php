<?php

namespace Iddigital\Cms\Core\Auth;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;

class Permission extends ValueObject implements IPermission
{
    /**
     * @var Permission[]
     */
    private static $permissionCache = [];

    /**
     * @var string
     */
    private $name;

    /**
     * Permission constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct();
        $this->name = $name;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->name)->asString();
    }

    /**
     * Constructs a permission with the supplied name.
     *
     * @param string $name
     *
     * @return Permission
     */
    public static function named($name)
    {
        if (!isset(self::$permissionCache[$name])) {
            self::$permissionCache[$name] = new self($name);
        }

        return self::$permissionCache[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function equals(IPermission $permission)
    {
        return $this->name === $permission->getName();
    }
}
