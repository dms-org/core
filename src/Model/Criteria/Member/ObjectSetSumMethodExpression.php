<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Type\IType;

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