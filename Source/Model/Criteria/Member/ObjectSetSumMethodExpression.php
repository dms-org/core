<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The object set sum method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectSetSumMethodExpression extends ObjectSetAggregateMethodExpression
{
    const METHOD_NAME = 'sum';

    /**
     * @inheritDoc
     */
    public function __construct(IType $sourceType, NestedMember $member)
    {
        parent::__construct(self::METHOD_NAME, $sourceType, $member);
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    protected function aggregateValues(array $values)
    {
        return array_sum($values);
    }
}