<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Type\IType;

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
    public function __construct(string $methodName, IType $sourceType, NestedMember $member)
    {
        parent::__construct($sourceType, $methodName, [$member->asString()], $member->getResultingType());

        $this->member = $member;
    }

    /**
     * @return NestedMember
     */
    public function getAggregatedMember() : \Dms\Core\Model\Criteria\NestedMember
    {
        return $this->member;
    }

    /**
     * @inheritDoc
     */
    public function isPropertyValue() : bool
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
    public function createArrayGetterCallable() : callable
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