<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Ordering;

use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Object\PropertyTypeDefiner;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestGroupEnum extends Enum
{
    const ONE = 'one';
    const TWO = 'two';
    const THREE = 'three';

    /**
     * Defines the type of options contained within the enum.
     *
     * @param PropertyTypeDefiner $values
     *
     * @return void
     */
    protected function defineEnumValues(PropertyTypeDefiner $values)
    {
        $values->asString();
    }

    public static function one()
    {
        return new self(self::ONE);
    }

    public static function two()
    {
        return new self(self::TWO);
    }

    public static function three()
    {
        return new self(self::THREE);
    }
}