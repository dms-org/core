<?php

namespace Iddigital\Cms\Core\Util;

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
    public function now()
    {
        return new \DateTime();
    }
}