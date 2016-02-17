<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema;

use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The static primary key column factory
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PrimaryKeyBuilder
{
    /**
     * @param string $name
     *
     * @return Column
     */
    public static function incrementingInt(string $name) : Column
    {
        return new Column($name, self::primaryKeyType()->autoIncrement(), true);
    }

    /**
     * Gets the column type for primary keys.
     *
     * @return Integer
     */
    public static function primaryKeyType() : Integer
    {
        return Integer::normal()->unsigned();
    }
}