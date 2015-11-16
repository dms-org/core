<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The object set aggregate method expression base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectSetAggregateMethodExpression extends ObjectSetMethodExpression
{
    /**
     * @var NestedMember
     */
    protected $member;

    /**
     * @param string       $methodName
     * @param IType        $sourceType
     * @param NestedMember $member
     */
    public function __construct($methodName, IType $sourceType, NestedMember $member)
    {
        parent::__construct($sourceType, $methodName, [$member->asString()], $member->getResultingType());

        $this->member = $member;
    }

    /**
     * @inheritDoc
     */
    public function isPropertyValue()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getProperty()
    {
        return null;
    }

    /**
     * @return \Closure
     */
    public function createArrayGetterCallable()
    {
        $memberGetter = $this->member->makeArrayGetterCallable();

        return function (array $objectSets) use ($memberGetter) {
            $results = [];

            foreach ($objectSets as $key => $objectSet) {
                /** @var IObjectSet|null $objectSet */

                if ($objectSet === null) {
                    $results[$key] = null;
                    continue;
                }

                $valuesToAggregate = array_values($memberGetter($objectSet->getAll()));

                $results[$key] = $valuesToAggregate
                        ? $this->aggregateValues($valuesToAggregate)
                        : null;
            }

            return $results;
        };
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    abstract protected function aggregateValues(array $values);
}