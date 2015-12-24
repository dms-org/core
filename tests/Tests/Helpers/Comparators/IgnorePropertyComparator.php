<?php

namespace Dms\Core\Tests\Helpers\Comparators;

use SebastianBergmann\Comparator\ObjectComparator;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IgnorePropertyComparator extends ObjectComparator
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string[]
     */
    private $propertiesToIgnore;

    /**
     * IgnorePropertyComparator constructor.
     *
     * @param string    $class
     * @param \string[] $propertiesToIgnore
     */
    public function __construct($class, array $propertiesToIgnore)
    {
        parent::__construct();
        $this->class              = $class;
        $this->propertiesToIgnore = array_fill_keys($propertiesToIgnore, true);
    }



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
        $class = $this->class;

        return $expected instanceof $class && $actual instanceof $class;
    }

    protected function toArray($object)
    {
        $properties = parent::toArray($object);

        return array_diff_key($properties, $this->propertiesToIgnore);
    }
}