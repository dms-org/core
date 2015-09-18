<?php

namespace Iddigital\Cms\Core\Model\Object\Type;

/**
 * The date value object.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Date extends DateOrTimeObject
{
    use DateOperations;

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function __construct($year, $month, $day)
    {
        $dateTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $dateTime = $dateTime->setDate($year, $month, $day);
        $dateTime = $dateTime->setTime(0, 0, 0);

        parent::__construct($dateTime);
    }

    /**
     * Creates a date object from the date part of the supplied date time.
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return Date
     */
    public static function fromNative(\DateTimeInterface $dateTime)
    {
        return new self($dateTime->format('Y'), $dateTime->format('m'), $dateTime->format('d'));
    }

    /**
     * Creates a date object from the supplied format string
     *
     * @param string $format
     * @param string $dateString
     *
     * @return Date
     */
    public static function fromFormat($format, $dateString)
    {
        return self::fromNative(\DateTimeImmutable::createFromFormat($format, $dateString));
    }

    /**
     * Returns whether the date is greater than the supplied date.
     *
     * @param Date $other
     *
     * @return bool
     */
    public function comesAfter(Date $other)
    {
        return $this->dateTime > $other->dateTime;
    }

    /**
     * Returns whether the date is less than the supplied date.
     *
     * @param Date $other
     *
     * @return bool
     */
    public function comesBefore(Date $other)
    {
        return $this->dateTime < $other->dateTime;
    }

    /**
     * Returns the amount of days between the supplied dates.
     *
     * @param Date $other
     *
     * @return int
     */
    public function daysBetween(Date $other)
    {
        return $this->dateTime->diff($other->dateTime, true)->days;
    }

    /**
     * Returns whether the date is equal to the supplied date.
     *
     * @param Date $other
     *
     * @return bool
     */
    public function equals(Date $other)
    {
        return $this->dateTime == $other->dateTime;
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
     * @param \DateTimeInterface $dateTime
     *
     * @return static
     */
    protected function createFromNativeObject(\DateTimeInterface $dateTime)
    {
        return self::fromNative($dateTime);
    }
}