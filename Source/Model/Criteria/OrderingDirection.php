<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The ordering direction interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
final class OrderingDirection
{
    const ASC = 'asc';
    const DESC = 'desc';

    private static $directions = [
            self::ASC  => true,
            self::DESC => true,
    ];

    private function __construct()
    {
    }

    /**
     * @return string[]
     */
    public static function getAll()
    {
        return array_keys(self::$directions);
    }

    /**
     * @param string $direction
     *
     * @return bool
     */
    public static function isValid($direction)
    {
        return isset(self::$directions[$direction]);
    }

    /**
     * @param string $direction
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validate($direction)
    {
        if (!isset(self::$directions[$direction])) {
            throw InvalidArgumentException::format(
                    'Invalid ordering direction: expecting one of (%s), \'%s\' given',
                    Debug::formatValues(array_keys(self::$directions)), $direction
            );
        }
    }
}