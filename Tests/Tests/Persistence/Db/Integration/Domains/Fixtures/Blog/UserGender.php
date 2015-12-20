<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog;

use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Object\PropertyTypeDefiner;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UserGender extends Enum
{
    const MALE = 'male';
    const FEMALE = 'female';

    public static function male()
    {
        return new self(self::MALE);
    }

    public static function female()
    {
        return new self(self::FEMALE);
    }

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
}