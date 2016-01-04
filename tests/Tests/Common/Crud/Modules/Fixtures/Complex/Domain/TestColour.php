<?php

namespace Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain;

use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Object\PropertyTypeDefiner;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestColour extends Enum
{
    const RED = 'red';
    const GREEN = 'green';
    const BLUE = 'blue';
    const YELLOW = 'yellow';

    /**
     * Defines the type of the options contained within the enum.
     *
     * @param PropertyTypeDefiner $values
     *
     * @return void
     */
    protected function defineEnumValues(PropertyTypeDefiner $values)
    {
        $values->asString();
    }

    /**
     * @return TestColour
     */
    public static function red()
    {
        return new self(self::RED);
    }

    /**
     * @return TestColour
     */
    public static function green()
    {
        return new self(self::GREEN);
    }

    /**
     * @return TestColour
     */
    public static function blue()
    {
        return new self(self::BLUE);
    }

    /**
     * @return TestColour
     */
    public static function yellow()
    {
        return new self(self::YELLOW);
    }
}