<?php

namespace Iddigital\Cms\Core\Persistence\Db\Doctrine\Migration\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * The doctrine types loader class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrineTypes
{
    private static $loaded = false;

    /**
     * @return void
     */
    public static function load()
    {
        if (self::$loaded) {
            return;
        }

        Type::addType(MediumIntType::MEDIUMINT, MediumIntType::class);
        Type::addType(TinyIntType::TINYINT, TinyIntType::class);

        self::$loaded = true;
    }

    public static function loadPlatform(AbstractPlatform $platform)
    {
        $platform->registerDoctrineTypeMapping(MediumIntType::MEDIUMINT, MediumIntType::MEDIUMINT);
        $platform->registerDoctrineTypeMapping(TinyIntType::TINYINT, TinyIntType::TINYINT);
    }
}