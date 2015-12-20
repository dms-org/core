<?php

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

    public function __construct($class, $name, PropertyAccessibility $accessibility)
    {
        $this->class         = $class;
        $this->name          = $name;
        $this->accessibility = $accessibility;

        $this->getter = \Closure::bind(function & (TypedObject $instance) use ($name) {
            return $instance->{$name};
        }, null, $class);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return PropertyAccessibility
     */
    public function getAccessibility()
    {
        return $this->accessibility;
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
    public function getType()
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
    public function hasType()
    {
        return $this->type !== null;
    }

    /**
     * @return bool
     */
    public function isIgnored()
    {
        return $this->ignored;
    }

    /**
     * @param bool $isIgnored
     */
    public function setIgnored($isIgnored)
    {
        $this->ignored = $isIgnored;
    }

    /**
     * @return bool
     */
    public function isImmutable()
    {
        return $this->immutable;
    }

    /**
     * @param bool $immutable
     */
    public function setImmutable($immutable)
    {
        $this->immutable = $immutable;
    }
}