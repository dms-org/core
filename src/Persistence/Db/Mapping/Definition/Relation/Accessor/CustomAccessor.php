<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\IAccessor;

/**
 * The custom accessor class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomAccessor implements IAccessor
{
    /**
     * @var callable
     */
    protected $getterCallback;

    /**
     * @var callable
     */
    protected $setterCallback;

    /**
     * CustomAccessor constructor.
     *
     * @param callable $getterCallback
     * @param callable $setterCallback
     */
    public function __construct(callable $getterCallback, callable $setterCallback)
    {
        $this->getterCallback = $getterCallback;
        $this->setterCallback = $setterCallback;
    }

    /**
     * @return callable
     */
    public function getGetterCallback() : callable
    {
        return $this->getterCallback;
    }

    /**
     * @return callable
     */
    public function getSetterCallback() : callable
    {
        return $this->setterCallback;
    }

    /**
     * @inheritDoc
     */
    public function getCompatibleType()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getDebugName() : string
    {
        return '<getter/setter>';
    }

    /**
     * @param ITypedObject $object
     * @param array        $properties
     *
     * @return mixed
     */
    public function get(ITypedObject $object, array $properties)
    {
        return call_user_func($this->getterCallback, $object);
    }

    /**
     * @param ITypedObject $object
     * @param array        $properties
     * @param mixed        $value
     *
     * @return void
     */
    public function set(ITypedObject $object, array &$properties, $value)
    {
        call_user_func($this->setterCallback, $object, $value);
    }
}