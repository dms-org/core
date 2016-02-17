<?php declare(strict_types = 1);

namespace Dms\Core\Util;

/**
 * The date time clock implementation.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeClock implements IClock
{
    /**
     * {@inheritDoc}
     */
    public function now() : \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /**
     * {@inheritDoc}
     */
    public function utcNow() : \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}