<?php

namespace Dms\Core\Tests\Helpers\Comparators;

use Dms\Core\Model\EntityCollection;
use SebastianBergmann\Comparator\ArrayComparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ObjectComparator;

/**
 * Compares only the contents of the collection.
 *
 * This avoids issues with differences in the identity map cache
 * which should be ignored.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityCollectionComparator extends ObjectComparator
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
        return $expected instanceof EntityCollection && $actual instanceof EntityCollection;
    }

    /**
     * Asserts that two values are equal.
     *
     * @param  EntityCollection $expected      The first value to compare
     * @param  EntityCollection $actual        The second value to compare
     * @param  float            $delta         The allowed numerical distance between two values to
     *                                         consider them equal
     * @param  bool             $canonicalize  If set to TRUE, arrays are sorted before
     *                                         comparison
     * @param  bool             $ignoreCase    If set to TRUE, upper- and lowercasing is
     *                                         ignored when comparing string values
     *
     * @param array             $processed
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = array())
    {
        $expectedArray = new EntityCollection($expected->getEntityType(), $expected);
        $actualArray   = new EntityCollection($actual->getEntityType(), $actual);
        parent::assertEquals($expectedArray, $actualArray, $delta, $canonicalize, $ignoreCase, $processed);
    }
}