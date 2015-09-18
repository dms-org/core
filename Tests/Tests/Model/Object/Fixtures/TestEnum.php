<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\Enum;
use Iddigital\Cms\Core\Model\Object\PropertyTypeDefiner;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestEnum extends Enum
{
    const ONE = 'one';
    const TWO = 'two';
    const THREE = 'three';

    protected function defineEnumValues(PropertyTypeDefiner $enum)
    {
        $enum->asString();
    }
}