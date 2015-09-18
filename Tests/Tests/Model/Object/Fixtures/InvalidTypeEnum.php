<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\Enum;
use Iddigital\Cms\Core\Model\Object\PropertyTypeDefiner;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidTypeEnum extends Enum
{
    const ONE = 'one';
    const TWO = 2;
    const THREE = 'three';

    protected function defineEnumValues(PropertyTypeDefiner $enum)
    {
        $enum->asInt();
    }
}