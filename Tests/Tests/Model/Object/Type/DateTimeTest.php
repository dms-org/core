<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Type;

use Iddigital\Cms\Core\Model\Object\Type\Date;
use Iddigital\Cms\Core\Model\Object\Type\DateTime;
use Iddigital\Cms\Core\Model\Object\Type\Time;
use Iddigital\Cms\Core\Model\Object\Type\TimeZonedDateTime;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeTest extends DateOrTimeObjectTest
{
    public function testNew()
    {
        $dateTime = new DateTime(new \DateTime('2015-03-05 12:00:01'));

        $this->assertInstanceOf(\DateTimeImmutable::class, $dateTime->getNativeDateTime());
        $this->assertSame('2015-03-05 12:00:01', $dateTime->getNativeDateTime()->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $dateTime->getNativeDateTime()->getTimezone()->getName());
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
        $dateTime = DateTime::fomFormat('d/m/Y', '21/8/2001');

        $this->assertSame(2001, $dateTime->getYear());
        $this->assertSame(8, $dateTime->getMonth());
        $this->assertSame(21, $dateTime->getDay());
        $this->assertSame(0, $dateTime->getHour());
        $this->assertSame(0, $dateTime->getMinute());
        $this->assertSame(0, $dateTime->getSecond());
    }

    public function testAddingAndSubtracting()
    {
        $dateTime = DateTime::fomFormat('Y-m-d H:i:s', '2001-01-01 12:00:00');

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
        $dateTime = DateTime::fomFormat('Y-m-d H:i:s', '2001-01-01 12:00:00');

        $this->assertFalse($dateTime->comesBefore($dateTime));
        $this->assertFalse($dateTime->comesBefore($dateTime->subSeconds(1)));
        $this->assertTrue($dateTime->comesBefore($dateTime->addSeconds(1)));

        $this->assertFalse($dateTime->comesAfter($dateTime));
        $this->assertFalse($dateTime->comesAfter($dateTime->addSeconds(1)));
        $this->assertTrue($dateTime->comesAfter($dateTime->subSeconds(1)));
    }

    public function testGetDateParts()
    {
        $dateTime = DateTime::fomFormat('Y-m-d H:i:s', '2001-01-01 12:00:00');

        $date = $dateTime->getDate();
        $this->assertInstanceOf(Date::class, $date);
        $this->assertSame(2001, $date->getYear());
        $this->assertSame(1, $date->getMonth());
        $this->assertSame(1, $date->getDay());
    }

    public function testGetTimeParts()
    {
        $dateTime = DateTime::fomFormat('Y-m-d H:i:s', '2001-01-01 12:00:00');

        $time = $dateTime->getTime();
        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(12, $time->getHour());
        $this->assertSame(0, $time->getMinute());
        $this->assertSame(0, $time->getSecond());
    }

    public function testInTimezone()
    {
        $dateTime = DateTime::fomFormat('Y-m-d H:i:s', '2001-01-01 12:00:00');

        $timezoned = $dateTime->inTimezone('Australia/Melbourne');
        $this->assertInstanceOf(TimeZonedDateTime::class, $timezoned);
        $this->assertSame('Australia/Melbourne', $timezoned->getTimezone()->getName());
        $this->assertFalse($dateTime->equals($timezoned));
        $this->assertSame('2001-01-01 12:00:00', $timezoned->format('Y-m-d H:i:s'));
    }

    public function testIgnoresTimezone()
    {
        $dateTime = new DateTime(new \DateTime('2001-01-01 12:00:00', new \DateTimeZone('Europe/Berlin')));

        $this->assertSame('UTC', $dateTime->getNativeDateTime()->getTimezone()->getName());
        $this->assertSame('2001-01-01 12:00:00', $dateTime->format('Y-m-d H:i:s'));
    }
}