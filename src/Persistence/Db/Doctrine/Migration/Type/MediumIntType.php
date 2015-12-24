<?php

namespace Dms\Core\Persistence\Db\Doctrine\Migration\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\Type;

/**
 * The doctrine medium int type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MediumIntType extends Type
{
    const MEDIUMINT = 'mediumint';

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return self::MEDIUMINT;
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array                                     $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform         The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'MEDIUMINT(10)';
    }
}