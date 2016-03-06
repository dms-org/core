<?php

namespace Dms\Core\Tests\Helpers\Comparators;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\ObjectCollection;
use SebastianBergmann\Comparator\ObjectComparator;

/**
 * Compares only the contents of the collection.
 *
 * This avoids issues with differences in the identity map cache
 * which should be ignored.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectCollectionComparator extends ObjectComparator
{

    /**
     * Returns whether the comparator can compare two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual   The second value to compare
     *
     * @return bool
     */
    public function accepts($expected, $actual)
    {
        return $expected instanceof ObjectCollection && $actual instanceof ObjectCollection;
    }

    protected function toArray($object)
    {
        /** @var EntityCollection $object */
        return $object->asArray();
    }
}