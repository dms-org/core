<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema;

use Dms\Core\Persistence\Db\Schema\Type\Integer;

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
        return new Column($name, Integer::normal()->unsigned()->autoIncrement(), true);
    }
}