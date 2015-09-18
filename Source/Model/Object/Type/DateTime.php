<?php

namespace Iddigital\Cms\Core\Model\Object\Type;

/**
 * The date time value object.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTime extends DateTimeBase
{
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