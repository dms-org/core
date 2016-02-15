<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Util\Debug;

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
    public static function getAll() : array
    {
        return array_keys(self::$directions);
    }

    /**
     * @param string $direction
     *
     * @return bool
     */
    public static function isValid(string $direction) : bool
    {
        return isset(self::$directions[$direction]);
    }

    /**
     * @param string $direction
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validate(string $direction)
    {
        if (!isset(self::$directions[$direction])) {
            throw InvalidArgumentException::format(
                    'Invalid ordering direction: expecting one of (%s), \'%s\' given',
                    Debug::formatValues(array_keys(self::$directions)), $direction
            );
        }
    }
}