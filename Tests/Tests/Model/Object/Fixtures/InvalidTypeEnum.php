<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Object\PropertyTypeDefiner;

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