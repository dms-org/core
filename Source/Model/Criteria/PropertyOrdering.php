<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;

/**
 * The property ordering class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyOrdering
{
    /**
     * @var NestedProperty
     */
    private $nestedProperty;

    /**
     * @var string
     */
    private $direction;

    /**
     * PropertyOrdering constructor.
     *
     * @param NestedProperty $nestedProperty
     * @param string         $direction
     *
     * @throws InvalidArgumentException
     */
    public function __construct(NestedProperty $nestedProperty, $direction)
    {
        OrderingDirection::validate($direction);

        $this->nestedProperty = $nestedProperty;
        $this->direction      = $direction;
    }

    /**
     * @return FinalizedPropertyDefinition[]
     */
    final  public function getNestedProperties()
    {
        return $this->nestedProperty->getNestedProperties();
    }

    /**
     * @return string
     */
    final public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @return bool
     */
    final public function isAsc()
    {
        return $this->direction === OrderingDirection::ASC;
    }

    /**
     * Returns a callable which takes a object as a parameter
     * and returns the value to order by.
     *
     * @return callable
     */
    public function getOrderCallable()
    {
        return $this->nestedProperty->makePropertyGetterCallable();
    }
}