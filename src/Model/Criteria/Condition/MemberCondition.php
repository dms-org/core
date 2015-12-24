<?php

namespace Dms\Core\Model\Criteria\Condition;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Util\Debug;

/**
 * The member condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberCondition extends OperatorCondition
{
    /**
     * @var NestedMember
     */
    private $member;

    /**
     * MemberCondition constructor.
     *
     * @param NestedMember $member
     * @param string       $conditionOperator
     * @param mixed        $value
     *
     * @throws InvalidArgumentException
     * @throws TypeMismatchException
     */
    final public function __construct(NestedMember $member, $conditionOperator, $value)
    {
        $this->member = $member;
        parent::__construct($member->getResultingType(), $conditionOperator, $value);
    }

    /**
     * @return string
     */
    protected function debugExpressionString()
    {
        return sprintf('member \'%s\'', $this->member->asString());
    }

    /**
     * @return NestedMember
     */
    final public function getNestedMember()
    {
        return $this->member;
    }

    /**
     * @return callable
     */
    protected function makeArrayGetterCallback()
    {
        return $this->member->makeArrayGetterCallable();
    }
}