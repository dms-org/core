<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * The member property expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberPropertyExpression extends MemberExpression
{
    /**
     * @var FinalizedPropertyDefinition
     */
    protected $property;

    /**
     * MemberPropertyExpression constructor.
     *
     * @param FinalizedPropertyDefinition $property
     * @param bool                        $isSourceNullable
     */
    public function __construct(FinalizedPropertyDefinition $property, $isSourceNullable)
    {
        $sourceType = Type::object($property->getAccessibility()->getDeclaredClass());

        parent::__construct($isSourceNullable ? $sourceType->nullable() : $sourceType, $property->getType());

        $this->property = $property;
    }

    /**
     * @inheritDoc
     */
    public function isPropertyValue()
    {
        return true;
    }

    /**
     * @inheritDoc
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
    public function createArrayGetterCallable()
    {
        $name = $this->property->getName();

        return $this->ensureAccessible(function (array $objects) use ($name) {
            $results = [];

            foreach ($objects as $key => $object) {
                $results[$key] = $object === null
                        ? null
                        : $object->{$name};
            }

            return $results;
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