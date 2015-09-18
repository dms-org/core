<?php

namespace Iddigital\Cms\Core\Tests;

use Iddigital\Cms\Core\Tests\Helpers\Comparators\EntityCollectionComparator;
use SebastianBergmann\Comparator\Factory;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomComparators
{
    public static function load()
    {
        Factory::getInstance()->register(new EntityCollectionComparator());
    }
}