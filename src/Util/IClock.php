<?php declare(strict_types = 1);

namespace Dms\Core\Util;

interface IClock
{
    /**
     * Gets the current time.
     *
     * @return \DateTimeImmutable
     */
    public function now();

    /**
     * Gets the current time in UTC.
     *
     * @return \DateTimeImmutable
     */
    public function utcNow();
}
