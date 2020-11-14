<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;
use Dms\Core\Model\Type\IType;

/**
 * The property definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyDefinition
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $name;

    /**
     * @var PropertyAccessibility
     */
    private $accessibility;

    /**
     * @var \Closure
     */
    private $getter;

    /**
     * @var IType
     */
    private $type;

    /**
     * @var bool
     */
    private $ignored = false;

    /**
     * @var bool
     */
    private $immutable = false;

    /**
     * @var \ReflectionProperty
     */
    private $reflection;

    public function __construct($class, $name, PropertyAccessibility $accessibility, \ReflectionProperty $reflection)
    {
        $this->class         = $class;
        $this->name          = $name;
        $this->accessibility = $accessibility;
        $this->reflection    = $reflection;

        $this->getter = \Closure::bind(function & (TypedObject $instance) use ($name) {
            return $instance->{$name};
        }, null, $class);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * @return PropertyAccessibility
     */
    public function getAccessibility() : PropertyAccessibility
    {
        return $this->accessibility;
    }

    /**
     * @return \ReflectionProperty
     */
    public function getReflection() : \ReflectionProperty
    {
        return $this->reflection;
    }

    /**
     * @return bool
     */
    public function canGetReference() : bool
    {
        return !$this->reflection->hasType() || $this->reflection->getType()->allowsNull();
    }

    /**
     * @param TypedObject $instance
     *
     * @return mixed
     */
    public function & getReferenceOn(TypedObject $instance)
    {
        $getter = $this->getter;

        return $getter($instance);
    }

    /**
     * @return IType
     */
    public function getType() : \Dms\Core\Model\Type\IType
    {
        return $this->type;
    }

    /**
     * @param IType $type
     */
    public function setType(IType $type)
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function hasType() : bool
    {
        return $this->type !== null;
    }

    /**
     * @return bool
     */
    public function isIgnored() : bool
    {
        return $this->ignored;
    }

    /**
     * @param bool $isIgnored
     */
    public function setIgnored(bool $isIgnored)
    {
        $this->ignored = $isIgnored;
    }

    /**
     * @return bool
     */
    public function isImmutable() : bool
    {
        return $this->immutable;
    }

    /**
     * @param bool $immutable
     */
    public function setImmutable(bool $immutable)
    {
        $this->immutable = $immutable;
    }
}