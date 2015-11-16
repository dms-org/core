<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\Type\CollectionType;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The object set minimum method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectSetMinimumMethodExpression extends ObjectSetAggregateMethodExpression
{
    const METHOD_NAME = 'min';

    /**
     * @inheritDoc
     */
    public function __construct(IType $sourceType, NestedMember $member)
    {
        parent::__construct(self::METHOD_NAME, $sourceType, $member);
    }

    /**
     * @inheritDoc
     */
    public function isPropertyValue()
    {
        return $this->member->isPropertyValue();
    }

    /**
     * @inheritDoc
     */
    public function getProperty()
    {
        return $this->member->getProperty();
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    protected function aggregateValues(array $values)
    {
        $min = $values[0];

        foreach ($values as $value) {
            if ($value < $min) {
                $min = $value;
            }
        }

        return $min;
    }
}