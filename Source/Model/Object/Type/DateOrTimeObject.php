<?php

namespace Iddigital\Cms\Core\Model\Object\Type;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IComparable;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;

/**
 * The date time object base
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class DateOrTimeObject extends ValueObject implements IComparable
{
    /**
     * @var \DateTimeImmutable
     */
    protected $dateTime;

    /**
     * DateTimeObject constructor.
     *
     * @param \DateTimeImmutable $dateTime
     */
    public function __construct(\DateTimeImmutable $dateTime)
    {
        parent::__construct();
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritDoc}
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->dateTime)->asObject(\DateTimeImmutable::class);
    }

    /**
     * Gets the internal date time object.
     *
     * @return \DateTimeImmutable
     */
    public function getNativeDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Formats the date/time as a string.
     *
     * @param string $format
     *
     * @return string
     */
    public function format($format)
    {
        return $this->dateTime->format($format);
    }

    /**
     * Returns a new object with the supplied interval added.
     *
     * @param \DateInterval $interval
     *
     * @return static
     */
    public function add(\DateInterval $interval)
    {
        return $this->createFromNativeObject($this->dateTime->add($interval));
    }

    /**
     * Returns a new object with the supplied interval subtracted.
     *
     * @param \DateInterval $interval
     *
     * @return static
     */
    public function sub(\DateInterval $interval)
    {
        return $this->createFromNativeObject($this->dateTime->sub($interval));
    }

    /**
     * @param string           $method
     * @param DateOrTimeObject $start
     * @param DateOrTimeObject $end
     * @param string           $format
     *
     * @throws InvalidArgumentException
     */
    final protected function verifyStartLessThenEnd($method, DateOrTimeObject $start, DateOrTimeObject $end, $format)
    {
        if ($start->dateTime > $end->dateTime) {
            throw InvalidArgumentException::format(
                    'Invalid start and end arguments passed to %s: start cannot be greater than end, start %s and end %s given',
                    $method, $start->format($format), $end->format($format)
            );
        }
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return static
     */
    abstract protected function createFromNativeObject(\DateTimeInterface $dateTime);
}