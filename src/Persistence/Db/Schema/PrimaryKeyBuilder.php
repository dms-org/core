<?php

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
    public static function incrementingInt($name)
    {
        return new Column($name, Integer::normal()->unsigned()->autoIncrement(), true);
    }
}