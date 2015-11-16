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
     * @inheritdoc
     */
    protected function makeArrayFilterCallable()
    {
        $innerCondition = $this->condition->getFilterCallable();

        return function (array $objects) use ($innerCondition) {
            return array_diff_key($objects, $innerCondition($objects));
        };
    }
}