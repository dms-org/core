<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;

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
    public function __construct(NestedMember $nestedMember, string $direction)
    {
        OrderingDirection::validate($direction);

        $this->nestedMember = $nestedMember;
        $this->direction      = $direction;
    }

    /**
     * @return NestedMember
     */
    final public function getNestedMember() : NestedMember
    {
        return $this->nestedMember;
    }

    /**
     * @return string
     */
    final public function getDirection() : string
    {
        return $this->direction;
    }

    /**
     * @return bool
     */
    final public function isAsc() : bool
    {
        return $this->direction === OrderingDirection::ASC;
    }

    /**
     * Returns a callable which takes an array of objects
     * and returns an array of values to order by.
     *
     * @return callable
     */
    public function getArrayOrderCallable() : callable
    {
        return $this->nestedMember->makeArrayGetterCallable();
    }
}