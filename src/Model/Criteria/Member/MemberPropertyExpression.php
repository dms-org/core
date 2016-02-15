<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Model\Type\Builder\Type;

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
    public function __construct(FinalizedPropertyDefinition $property, bool $isSourceNullable)
    {
        $sourceType = Type::object($property->getAccessibility()->getDeclaredClass());

        parent::__construct(
                $isSourceNullable ? $sourceType->nullable() : $sourceType,
                $property->getType(),
                $property->getName()
        );

        $this->property = $property;
    }

    /**
     * @inheritDoc
     */
    public function isPropertyValue() : bool
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
    public function createArrayGetterCallable() : callable
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