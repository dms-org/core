<?php

namespace Dms\Core\Tests;

use Dms\Core\Tests\Helpers\Comparators\ObjectCollectionComparator;
use SebastianBergmann\Comparator\Factory;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomComparators
{
    public static function load()
    {
        Factory::getInstance()->register(new ObjectCollectionComparator());
    }
}