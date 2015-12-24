<?php

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;
use Dms\Core\Model\Type\IType;

/**
 * The finalized property definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedPropertyDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var IType
     */
    private $type;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var PropertyAccessibility
     */
    private $accessibility;

    /**
     * @var bool
     */
    private $immutable;

    /**
     * FinalizedPropertyDefinition constructor.
     *
     * @param string                $name
     * @param IType                 $type
     * @param mixed                 $defaultValue
     * @param PropertyAccessibility $accessibility
     * @param bool                  $immutable
     */
    public function __construct(
            $name,
            IType $type,
            $defaultValue,
            PropertyAccessibility $accessibility,
            $immutable
    ) {
        $this->name          = $name;
        $this->type          = $type;
        $this->defaultValue  = $defaultValue;
        $this->accessibility = $accessibility;
        $this->immutable     = $immutable;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return IType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return PropertyAccessibility
     */
    public function getAccessibility()
    {
        return $this->accessibility;
    }

    /**
     * @return boolean
     */
    public function isImmutable()
    {
        return $this->immutable;
    }

    /**
     * @return self
     */
    public function asNullable()
    {
        return new self(
                $this->name,
                $this->type->nullable(),
                $this->defaultValue,
                $this->accessibility,
                $this->immutable
        );
    }
}