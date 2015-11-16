<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;

/**
 * The member ordering class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberOrdering
{
    /**
     * @var NestedMember
     */
    private $nestedMember;

    /**
     * @var string
     */
    private $direction;

    /**
     * PropertyOrdering constructor.
     *
     * @param NestedMember $nestedMember
     * @param string         $direction
     *
     * @throws InvalidArgumentException
     */
    public function __construct(NestedMember $nestedMember, $direction)
    {
        OrderingDirection::validate($direction);

        $this->nestedMember = $nestedMember;
        $this->direction      = $direction;
    }

    /**
     * @return IMemberExpressionParser[]
     */
    final public function getNestedMembers()
    {
        return $this->nestedMember->getParts();
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
     * Returns a callable which takes an array of objects
     * and returns an array of values to order by.
     *
     * @return callable
     */
    public function getArrayOrderCallable()
    {
        return $this->nestedMember->makeArrayGetterCallable();
    }
}