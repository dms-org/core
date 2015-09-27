<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Type;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\Type\Date;
use Iddigital\Cms\Core\Model\Object\Type\DateTime;
use Iddigital\Cms\Core\Model\Object\Type\TimeZonedDateTime;
use Iddigital\Cms\Core\Model\Object\Type\Time;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TimezonedDateTimeTest extends DateOrTimeObjectTest
{
    public function testNew()
    {
        $dateTime = new TimeZonedDateTime(new \DateTime('2015-03-05 12:00:01', new \DateTimeZone('Australia/Melbourne')));

        $this->assertInstanceOf(\DateTimeImmutable::class, $dateTime->getNativeDateTime());
        $this->assertSame('2015-03-05 12:00:01', $dateTime->getNativeDateTime()->format('Y-m-d H:i:s'));
        $this->assertSame('Australia/Melbourne', $dateTime->getNativeDateTime()->getTimezone()->getName());
        $this->assertSame('Australia/Melbourne', $dateTime->getTimezone()->getName());
        $this->assertSame(2015, $dateTime->getYear());
        $this->assertSame(3, $dateTime->getMonth());
        $this->assertSame(5, $dateTime->getDay());
        $this->assertSame(12, $dateTime->getHour());
        $this->assertSame(0, $dateTime->getMinute());
        $this->assertSame(1, $dateTime->getSecond());
        $this->assertSame($dateTime->getNativeDateTime()->getTimestamp(), $dateTime->getTimestamp());
        $this->assertSame('2015-03-05 12:00:01', $dateTime->format('Y-m-d H:i:s'));
        $this->assertSame(true, $dateTime->equals($dateTime));
        $this->assertSame(true, $dateTime->equals(clone $dateTime));
    }

    public function testFromFormat()
    {
        $dateTime = TimeZonedDateTime::fromFormat('d/m/Y', '21/8/2001', 'Australia/Melbourne');

        $this->assertSame('Australia/Melbourne', $dateTime->getTimezone()->getName());
        $this->assertSame(2001, $dateTime->getYear());
        $this->assertSame(8, $dateTime->getMonth());
        $this->assertSame(21, $dateTime->getDay());
        $this->assertSame(0, $dateTime->getHour());
        $this->assertSame(0, $dateTime->getMinute());
        $this->assertSame(0, $dateTime->getSecond());
    }

    public function testAddingAndSubtracting()
    {
        $dateTime = TimeZonedDateTime::fromFormat('Y-m-d H:i:s', '2001-01-01 12:00:00', 'UTC');

        $otherTime = $dateTime
                ->addYears(10)
                ->subMonths(3)
                ->addDays(20)
                ->subHours(3)
                ->addMinutes(12)
                ->subSeconds(36);

        $this->assertNotSame($dateTime, $otherTime);
        $this->assertSame('2001-01-01 12:00:00', $dateTime->format('Y-m-d H:i:s'));
        $this->assertSame('2010-10-21 09:11:24', $otherTime->format('Y-m-d H:i:s'));
    }

    public function testComparisons()
    {
        $dateTime = TimeZonedDateTime::fromFormat('Y-m-d H:i:s', '2001-01-01 12:00:00', 'Australia/Melbourne');

        $this->assertFalse($dateTime->comesBefore($dateTime));
        $this->assertFalse($dateTime->comesBefore($dateTime->subSeconds(1)));
        $this->assertTrue($dateTime->comesBefore($dateTime->addSeconds(1)));

        $this->assertTrue($dateTime->comesBeforeOrEqual($dateTime));
        $this->assertFalse($dateTime->comesBeforeOrEqual($dateTime->subSeconds(1)));
        $this->assertTrue($dateTime->comesBeforeOrEqual($dateTime->addSeconds(1)));

        $this->assertFalse($dateTime->comesAfter($dateTime));
        $this->assertFalse($dateTime->comesAfter($dateTime->addSeconds(1)));
        $this->assertTrue($dateTime->comesAfter($dateTime->subSeconds(1)));

        $this->assertTrue($dateTime->comesAfterOrEqual($dateTime));
        $this->assertFalse($dateTime->comesAfterOrEqual($dateTime->addSeconds(1)));
        $this->assertTrue($dateTime->comesAfterOrEqual($dateTime->subSeconds(1)));
    }

    public function testBetween()
    {
        $dateTime = TimeZonedDateTime::fromFormat('Y-m-d H:i:s', '2001-01-01 12:00:00', 'Australia/Melbourne');

        $this->assertTrue($dateTime->isBetween($dateTime->subSeconds(1), $dateTime->addSeconds(1)));
        $this->assertFalse($dateTime->isBetween($dateTime, $dateTime->addSeconds(1)));
        $this->assertFalse($dateTime->isBetween($dateTime->subSeconds(1), $dateTime));
        $this->assertFalse($dateTime->isBetween($dateTime, $dateTime));
        $this->assertFalse($dateTime->isBetween($dateTime->addSeconds(1), $dateTime->addSeconds(2)));
        $this->assertFalse($dateTime->isBetween($dateTime->subSeconds(2), $dateTime->subSeconds(1)));

        $this->assertTrue($dateTime->isBetweenInclusive($dateTime->subSeconds(1), $dateTime->addSeconds(1)));
        $this->assertTrue($dateTime->isBetweenInclusive($dateTime, $dateTime->addSeconds(1)));
        $this->assertTrue($dateTime->isBetweenInclusive($dateTime->subSeconds(1), $dateTime));
        $this->assertTrue($dateTime->isBetweenInclusive($dateTime, $dateTime));
        $this->assertFalse($dateTime->isBetweenInclusive($dateTime->addSeconds(1), $dateTime->addSeconds(2)));

        $this->assertThrows(function () use ($dateTime) {
            $dateTime->isBetween($dateTime->addSeconds(1), $dateTime);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($dateTime) {
            $dateTime->isBetweenInclusive($dateTime->addSeconds(1), $dateTime);
        }, InvalidArgumentException::class);
    }

    public function testGetDateParts()
    {
        $dateTime = TimeZonedDateTime::fromFormat('Y-m-d H:i:s', '2001-01-01 12:00:00', 'Australia/Melbourne');

        $date = $dateTime->getDate();
        $this->assertInstanceOf(Date::class, $date);
        $this->assertSame(2001, $date->getYear());
        $this->assertSame(1, $date->getMonth());
        $this->assertSame(1, $date->getDay());
    }

    public function testGetTimeParts()
    {
        $dateTime = TimeZonedDateTime::fromFormat('Y-m-d H:i:s', '2001-01-01 12:00:00', 'Australia/Melbourne');

        $time = $dateTime->getTime();
        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(12, $time->getHour());
        $this->assertSame(0, $time->getMinute());
        $this->assertSame(0, $time->getSecond());
    }

    public function testConvertTimezone()
    {
        $dateTime = TimeZonedDateTime::fromFormat('Y-m-d H:i:s', '2001-01-01 12:00:00', 'Australia/Melbourne');

        $timezoned = $dateTime->convertTimezone('UTC');
        $this->assertInstanceOf(TimeZonedDateTime::class, $timezoned);
        $this->assertSame('UTC', $timezoned->getTimezone()->getName());
        $this->assertFalse($dateTime->equals($timezoned));
        $this->assertSame('2001-01-01 12:00:00', $dateTime->format('Y-m-d H:i:s'));
        $this->assertSame('2001-01-01 01:00:00', $timezoned->format('Y-m-d H:i:s'));
    }

    public function testRegardlessOfTimezone()
    {
        $dateTime = TimeZonedDateTime::fromFormat('Y-m-d H:i:s', '2001-01-01 12:00:00', 'Australia/Melbourne')
                ->regardlessOfTimezone();

        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertSame('UTC', $dateTime->getNativeDateTime()->getTimezone()->getName());
        $this->assertSame('2001-01-01 12:00:00', $dateTime->format('Y-m-d H:i:s'));
    }
}