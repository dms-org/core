<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Model\Criteria\IMemberExpression;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * The member property expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberPropertyExpression implements IMemberExpression
{
    /**
     * @var FinalizedPropertyDefinition
     */
    protected $property;

    /**
     * @var bool
     */
    protected $isSourceNullable;

    /**
     * MemberPropertyExpression constructor.
     *
     * @param FinalizedPropertyDefinition $property
     * @param bool                        $isSourceNullable
     */
    public function __construct(FinalizedPropertyDefinition $property, $isSourceNullable)
    {
        $this->property         = $property;
        $this->isSourceNullable = $isSourceNullable;
    }

    /**
     * @return boolean
     */
    public function isIsSourceNullable()
    {
        return $this->isSourceNullable;
    }

    /**
     * @return FinalizedPropertyDefinition
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @inheritDoc
     */
    public function asString()
    {
        return $this->property->getName();
    }

    /**
     * @inheritDoc
     */
    public function getSourceType()
    {
        $type = Type::object($this->property->getAccessibility()->getDeclaredClass());

        return $this->isSourceNullable
                ? $type->nullable()
                : $type;
    }

    /**
     * @inheritDoc
     */
    public function getResultingType()
    {
        $type = $this->property->getType();

        return $this->isSourceNullable
                ? $type->nullable()
                : $type;
    }

    /**
     * @inheritDoc
     */
    public function createGetterCallable()
    {
        $name = $this->property->getName();

        return $this->ensureAccessible(function ($object) use ($name) {
            return $object === null
                    ? null
                    : $object->{$name};
        });
    }

    /**
     * @param \Closure $closure
     *
     * @return \Closure
     */
    final protected function ensureAccessible(\Closure $closure)
    {
        return \Closure::bind($closure, null, $this->property->getAccessibility()->getDeclaredClass());
    }
}