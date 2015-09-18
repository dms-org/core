<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;

/**
 * The composite condition base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class CompositeCondition extends Condition
{
    /**
     * @var Condition[]
     */
    protected $conditions;

    /**
     * CompositeCondition constructor.
     *
     * @param Condition[] $conditions
     */
    public function __construct(array $conditions)
    {
        InvalidArgumentException::verify(count($conditions) > 1, 'conditions must have greater than one inner condition');
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'conditions', $conditions, Condition::class);

        $this->conditions = [];
        $class            = get_class($this);
        foreach ($conditions as $condition) {
            if ($condition instanceof $class) {
                /** @var CompositeCondition $condition */
                foreach ($condition->getConditions() as $inner) {
                    $this->conditions[] = $inner;
                }
            } else {
                $this->conditions[] = $condition;
            }
        }

        parent::__construct();
    }

    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }
}