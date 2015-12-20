<?php

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * The object set average method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectSetAverageMethodExpression extends ObjectSetAggregateMethodExpression
{
    const METHOD_NAME = 'average';

    /**
     * @inheritDoc
     */
    public function __construct(IType $sourceType, NestedMember $member)
    {
        parent::__construct(self::METHOD_NAME, $sourceType, $member);

        $this->resultType = Type::float();
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    protected function aggregateValues(array $values)
    {
        return (float)(array_sum($values) / count($values));
    }
}