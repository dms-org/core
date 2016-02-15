<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine\Migration\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * The base doctrine enum type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class BaseEnumType extends Type
{
    /**
     * The values within the enum.
     *
     * @var string[]
     */
    protected static $values = [];

    /**
     * @return string
     */
    public static function getTypeName() : string
    {
        return 'enum(' . implode(',', static::$values) . ')';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return static::getTypeName();
    }

    /**
     * @return string[]
     */
    public function getValues() : array
    {
        return static::$values;
    }

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $quoted = [];

        foreach (static::$values as $value) {
            $quoted[] = $platform->quoteStringLiteral($value);
        }

        return 'ENUM(' . implode(',', $quoted) . ')';
    }
}