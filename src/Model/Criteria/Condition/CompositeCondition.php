<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Condition;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The composite condition base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class CompositeCondition extends Condition
{
    /**
     * CompositeCondition constructor.
     *
     * @param Condition[] $conditions
     */
    public function __construct(array $conditions)
    {
        InvalidArgumentException::verify(count($conditions) > 1, 'conditions must have greater than one inner condition');
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'conditions', $conditions, Condition::class);

        $unwrappedConditions = [];
        $class            = get_class($this);
        foreach ($conditions as $condition) {
            if ($condition instanceof $class) {
                /** @var CompositeCondition $condition */
                foreach ($condition->getConditions() as $inner) {
                    $unwrappedConditions[] = $inner;
                }
            } else {
                $unwrappedConditions[] = $condition;
            }
        }

        parent::__construct($unwrappedConditions);
    }

    /**
     * @return Condition[]
     */
    public function getConditions() : array
    {
        return $this->children;
    }
}