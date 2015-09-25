<?php

namespace Iddigital\Cms\Core\Model\Object\Type;

/**
 * The date time base value object.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class DateTimeBase extends DateOrTimeObject
{
    use DateOperations;
    use TimeOperations;

    /**
     * @param \DateTimeImmutable $dateTime
     */
    public function __construct(\DateTimeImmutable $dateTime)
    {
        parent::__construct($dateTime);
    }

    /**
     * Returns a diff of the supplied date time.
     *
     * @param DateTimeBase $other
     * @param bool         $absolute
     *
     * @return \DateInterval
     */
    public function diff(DateTimeBase $other, $absolute = false)
    {
        return $this->dateTime->diff($other->dateTime, $absolute);
    }

    /**
     * Returns whether the DateTimeBase is equal to the supplied date.
     *
     * @param DateTimeBase $other
     *
     * @return bool
     */
    public function equals(DateTimeBase $other)
    {
        $dateTime      = $this->dateTime;
        $otherDateTime = $other->dateTime;

        return $dateTime == $otherDateTime
                && $dateTime->getTimezone()->getName() === $otherDateTime->getTimezone()->getName();
    }

    /**
     * Gets the timestamp (seconds from epoch).
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * Gets the date part of the date time.
     *
     * @return Date
     */
    public function getDate()
    {
        return Date::fromNative($this->dateTime);
    }

    /**
     * Gets the time part of the date time.
     *
     * @return Time
     */
    public function getTime()
    {
        return Time::fromNative($this->dateTime);
    }
}