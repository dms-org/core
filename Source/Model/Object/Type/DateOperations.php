<?php

namespace Iddigital\Cms\Core\Model\Object\Type;

/**
 * The date operations trait.
 *
 * @property \DateTimeImmutable dateTime
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
trait DateOperations
{
    /**
     * Gets the year
     *
     * @return int
     */
    public function getYear()
    {
        return (int)$this->dateTime->format('Y');
    }

    /**
     * Gets the month
     *
     * @return int
     */
    public function getMonth()
    {
        return (int)$this->dateTime->format('m');
    }

    /**
     * Gets the day
     *
     * @return int
     */
    public function getDay()
    {
        return (int)$this->dateTime->format('d');
    }

    /**
     * Returns a new date with the supplied amount of days added.
     *
     * @param int $days
     *
     * @return static
     */
    public function addDays($days)
    {
        return $this->add(\DateInterval::createFromDateString($days . ' days'));
    }

    /**
     * Returns a new date with the supplied amount of days subtracted.
     *
     * @param int $days
     *
     * @return static
     */
    public function subDays($days)
    {
        return $this->addDays(-$days);
    }

    /**
     * Returns a new date with the supplied amount of weeks added.
     *
     * @param int $weeks
     *
     * @return static
     */
    public function addWeeks($weeks)
    {
        return $this->addDays(7 * $weeks);
    }

    /**
     * Returns a new date with the supplied amount of weeks subtracted.
     *
     * @param int $weeks
     *
     * @return static
     */
    public function subWeeks($weeks)
    {
        return $this->addDays(7 * -$weeks);
    }

    /**
     * Returns a new date with the supplied amount of months added.
     *
     * @param int $months
     *
     * @return static
     */
    public function addMonths($months)
    {
        return $this->add(\DateInterval::createFromDateString($months . ' months'));
    }

    /**
     * Returns a new date with the supplied amount of months subtracted.
     *
     * @param int $months
     *
     * @return static
     */
    public function subMonths($months)
    {
        return $this->addMonths(-$months);
    }

    /**
     * Returns a new date with the supplied amount of years added.
     *
     * @param int $years
     *
     * @return static
     */
    public function addYears($years)
    {
        return $this->add(\DateInterval::createFromDateString($years . ' years'));
    }

    /**
     * Returns a new date with the supplied amount of years subtracted.
     *
     * @param int $years
     *
     * @return static
     */
    public function subYears($years)
    {
        return $this->addYears(-$years);
    }

    /**
     * @param \DateInterval $interval
     *
     * @return static
     */
    abstract protected function add(\DateInterval $interval);
}