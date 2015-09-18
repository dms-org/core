<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Model\ITypedObject;

/**
 * The not condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NotCondition extends Condition
{
    /**
     * @var Condition
     */
    protected $condition;

    /**
     * NotCondition constructor.
     *
     * @param Condition $condition
     */
    public function __construct(Condition $condition)
    {
        $this->condition = $condition;
        parent::__construct();
    }

    /**
     * @return Condition
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Returns a callable that takes an object and returns a boolean
     * whether the object passes the condition.
     *
     * @return callable
     */
    protected function makeFilterCallable()
    {
        $innerCondition = $this->condition->getFilterCallable();

        return function (ITypedObject $object) use ($innerCondition) {
            return !$innerCondition($object);
        };
    }
}