<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Model\ValueObjectCollection;

class Permission extends ValueObject implements IPermission
{
    const NAME = 'name';

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
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $name)
    {
        if (empty($name)) {
            throw InvalidArgumentException::format('Invalid call to %s: name cannot be empty', __METHOD__);
        }

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
    public static function named(string $name) : Permission
    {
        if (!isset(self::$permissionCache[$name])) {
            self::$permissionCache[$name] = new self($name);
        }

        return self::$permissionCache[$name];
    }

    /**
     * @param string[] $names
     *
     * @return ValueObjectCollection
     */
    public static function collectionFromNames(array $names) : ValueObjectCollection
    {
        $permissions = [];

        foreach ($names as $name) {
            $permissions[] = self::named($name);
        }

        return self::collection($permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function inNamespace(string $namespace)
    {
        return self::named($namespace . '.' . $this->name);
    }

    /**
     * {@inheritDoc}
     */
    public function equals(IPermission $permission) : bool
    {
        return $this->name === $permission->getName();
    }

    /**
     * Namespaces all the permissions.
     *
     * @param IPermission[] $permissions
     * @param string        $namespace
     *
     * @return IPermission[]
     */
    public static function namespaceAll(array $permissions, string $namespace) : array
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'permissions', $permissions, IPermission::class);

        $namespacedPermissions = [];

        foreach ($permissions as $permission) {
            $permission = $permission->inNamespace($namespace);

            $namespacedPermissions[$permission->getName()] = $permission;
        }

        return $namespacedPermissions;
    }
}
