<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Condition;

use Dms\Core\Model\ITypedObject;

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
    public function getCondition() : Condition
    {
        return $this->condition;
    }

    /**
     * @inheritdoc
     */
    protected function makeArrayFilterCallable() : callable
    {
        $innerCondition = $this->condition->getFilterCallable();

        return function (array $objects) use ($innerCondition) {
            return array_diff_key($objects, $innerCondition($objects));
        };
    }
}