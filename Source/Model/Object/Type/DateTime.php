<?php

namespace Iddigital\Cms\Core\Model\Object\Type;

/**
 * The date time value object.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTime extends DateTimeBase
{
    private static $debugFormat = 'Y-m-d H:i:s';

    /**
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        parent::__construct(
                \DateTimeImmutable::createFromFormat(
                        'Y-m-d H:i:s',
                        $dateTime->format('Y-m-d H:i:s'),
                        new \DateTimeZone('UTC')
                )
        );
    }

    /**
     * Creates a DateTime object from the supplied date string
     *
     * @param string $dateTimeString
     *
     * @return DateTime
     */
    public static function fromString($dateTimeString)
    {
        return new self(new \DateTimeImmutable(
                $dateTimeString,
                new \DateTimeZone('UTC')
        ));
    }

    /**
     * Creates a DateTime object from the supplied format string
     *
     * @param string $format
     * @param string $dateString
     *
     * @return DateTime
     */
    public static function fomFormat($format, $dateString)
    {
        return new self(\DateTimeImmutable::createFromFormat('!' . $format, $dateString));
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return static
     */
    protected function createFromNativeObject(\DateTimeInterface $dateTime)
    {
        return new self($dateTime);
    }

    /**
     * Returns whether the datetime is greater than the supplied datetime.
     *
     * @param DateTime $other
     *
     * @return bool
     */
    public function comesAfter(DateTime $other)
    {
        return $this->dateTime > $other->dateTime;
    }

    /**
     * Returns whether the datetime is greater or equal to the supplied datetime.
     *
     * @param DateTime $other
     *
     * @return bool
     */
    public function comesAfterOrEqual(DateTime $other)
    {
        return $this->dateTime >= $other->dateTime;
    }

    /**
     * Returns whether the datetime is less than the supplied datetime.
     *
     * @param DateTime $other
     *
     * @return bool
     */
    public function comesBefore(DateTime $other)
    {
        return $this->dateTime < $other->dateTime;
    }

    /**
     * Returns whether the datetime is less or equal to the supplied datetime.
     *
     * @param DateTime $other
     *
     * @return bool
     */
    public function comesBeforeOrEqual(DateTime $other)
    {
        return $this->dateTime <= $other->dateTime;
    }

    /**
     * Returns whether the datetime is between the start and end datetime.
     *
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return bool
     */
    public function isBetween(DateTime $start, DateTime $end)
    {
        $this->verifyStartLessThenEnd(__METHOD__, $start, $end, self::$debugFormat);
        return $this->comesAfter($start) && $this->comesBefore($end);
    }

    /**
     * Returns whether the datetime is between the start and end datetime
     * or if it is equal to the start or end datetime.
     *
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return bool
     */
    public function isBetweenInclusive(DateTime $start, DateTime $end)
    {
        $this->verifyStartLessThenEnd(__METHOD__, $start, $end, self::$debugFormat);
        return $this->comesAfterOrEqual($start) && $this->comesBeforeOrEqual($end);
    }

    /**
     * Returns the current date time as if it were in the supplied timezone
     *
     * @param string $timeZoneId
     *
     * @return TimeZonedDateTime
     */
    public function inTimezone($timeZoneId)
    {
        return new TimeZonedDateTime(
                \DateTimeImmutable::createFromFormat(
                        'Y-m-d H:i:s',
                        $this->format('Y-m-d H:i:s'),
                        new \DateTimeZone($timeZoneId)
                )
        );
    }
}