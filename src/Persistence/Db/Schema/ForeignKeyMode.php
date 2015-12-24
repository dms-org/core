<?php

namespace Dms\Core\Persistence\Db\Schema;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Util\Debug;

/**
 * The foreign key mode enum class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyMode
{
    const CASCADE = 'cascade';
    const SET_NULL = 'set-null';
    const DO_NOTHING = 'do-nothing';

    private static $modes = [
            self::CASCADE,
            self::SET_NULL,
            self::DO_NOTHING
    ];

    /**
     * @param string $mode
     *
     * @return bool
     */
    public static function isValid($mode)
    {
        return in_array($mode, self::$modes, true);
    }

    /**
     * @param string $mode
     *
     * @throws InvalidArgumentException
     */
    public static function validate($mode)
    {
        if (!self::isValid($mode)) {
            throw InvalidArgumentException::format(
                    'Invalid foreign key mode: expecting one of (%s), %s given',
                    Debug::formatValues(self::$modes), $mode
            );
        }
    }
}