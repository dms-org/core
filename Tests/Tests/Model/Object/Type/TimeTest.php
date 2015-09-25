<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Type;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\Type\Time;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TimeTest extends DateOrTimeObjectTest
{
    public function testNew()
    {
        $time = new Time(15, 3, 37);

        $this->assertInstanceOf(\DateTimeImmutable::class, $time->getNativeDateTime());
        $this->assertSame('1970-01-01 15:03:37', $time->getNativeDateTime()->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $time->getNativeDateTime()->getTimezone()->getName());
        $this->assertSame(15, $time->getHour());
        $this->assertSame(3, $time->getMinute());
        $this->assertSame(37, $time->getSecond());
        $this->assertSame(true, $time->isPM());
        $this->assertSame(false, $time->isAM());
        $this->assertSame('15:03:37', $time->format('H:i:s'));
        $this->assertSame(false, $time->beforeNoon());
        $this->assertSame(true, $time->afterNoon());
        $this->assertSame(true, $time->equals($time));
        $this->assertSame(true, $time->equals(clone $time));
    }

    public function testFromNativeObject()
    {
        $time = Time::fromNative(new \DateTime('01:05 AM'));
        $this->assertSame(1, $time->getHour());
        $this->assertSame(5, $time->getMinute());
        $this->assertSame(0, $time->getSecond());
        $this->assertSame(false, $time->isPM());
        $this->assertSame(true, $time->isAM());
    }

    public function testFromFormat()
    {
        $time = Time::fromFormat('g:i A', '01:05 PM');

        $this->assertSame(13, $time->getHour());
        $this->assertSame(5, $time->getMinute());
        $this->assertSame(0, $time->getSecond());
        $this->assertSame(true, $time->isPM());
        $this->assertSame(false, $time->isAM());
    }

    public function testAddingAndSubtracting()
    {
        $time = new Time(12, 0, 0);

        $otherTime = $time->addHours(2)->subMinutes(5)->addSeconds(30);

        $this->assertNotSame($time, $otherTime);
        $this->assertSame('12:00:00', $time->format('H:i:s'));
        $this->assertSame('13:55:30', $otherTime->format('H:i:s'));
    }

    public function testComparisons()
    {
        $time = new Time(12, 0, 0);

        $this->assertFalse($time->isEarlierThan($time));
        $this->assertFalse($time->isEarlierThan($time->subSeconds(1)));
        $this->assertTrue($time->isEarlierThan($time->addSeconds(1)));

        $this->assertTrue($time->isEarlierThanOrEqual($time));
        $this->assertFalse($time->isEarlierThanOrEqual($time->subSeconds(1)));
        $this->assertTrue($time->isEarlierThanOrEqual($time->addSeconds(1)));

        $this->assertFalse($time->isLaterThan($time));
        $this->assertFalse($time->isLaterThan($time->addSeconds(1)));
        $this->assertTrue($time->isLaterThan($time->subSeconds(1)));

        $this->assertTrue($time->isLaterThanOrEqual($time));
        $this->assertFalse($time->isLaterThanOrEqual($time->addSeconds(1)));
        $this->assertTrue($time->isLaterThanOrEqual($time->subSeconds(1)));
    }

    public function testBetween()
    {
        $time = new Time(12, 0, 0);

        $this->assertTrue($time->isBetween($time->subSeconds(1), $time->addSeconds(1)));
        $this->assertFalse($time->isBetween($time, $time->addSeconds(1)));
        $this->assertFalse($time->isBetween($time->subSeconds(1), $time));
        $this->assertFalse($time->isBetween($time, $time));
        $this->assertFalse($time->isBetween($time->addSeconds(1), $time->addSeconds(2)));

        $this->assertTrue($time->isBetweenInclusive($time->subSeconds(1), $time->addSeconds(1)));
        $this->assertTrue($time->isBetweenInclusive($time, $time->addSeconds(1)));
        $this->assertTrue($time->isBetweenInclusive($time->subSeconds(1), $time));
        $this->assertTrue($time->isBetweenInclusive($time, $time));

        $this->assertThrows(function () use ($time) {
            $time->isBetween($time->addSeconds(1), $time);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($time) {
            $time->isBetweenInclusive($time->addSeconds(1), $time);
        }, InvalidArgumentException::class);
    }

    public function testNoonComparisons()
    {
        $time = new Time(12, 0, 0);

        $this->assertTrue($time->isNoon());
        $this->assertFalse($time->subSeconds(1)->isNoon());
        $this->assertFalse($time->addSeconds(1)->isNoon());

        $this->assertFalse($time->beforeNoon());
        $this->assertFalse($time->afterNoon());

        $this->assertTrue($time->subSeconds(1)->beforeNoon());
        $this->assertTrue($time->addSeconds(1)->afterNoon());
    }
    public function testFromString()
    {
        $time = Time::fromString('13:05:43');

        $this->assertSame(13, $time->getHour());
        $this->assertSame(5, $time->getMinute());
        $this->assertSame(43, $time->getSecond());

        $time = Time::fromString('13:05');

        $this->assertSame(13, $time->getHour());
        $this->assertSame(5, $time->getMinute());
        $this->assertSame(0, $time->getSecond());

        $time = Time::fromString('13');

        $this->assertSame(13, $time->getHour());
        $this->assertSame(0, $time->getMinute());
        $this->assertSame(0, $time->getSecond());
    }
    public function testFrtomString()
    {
        $this->assertTrue(Time::fromString('13:05:43') > Time::fromString('13:05:42'));
        $this->assertTrue(Time::fromString('13:05:43') >= Time::fromString('13:05:42'));
        $this->assertTrue(Time::fromString('03:05:43') < Time::fromString('13:05:42'));
        $this->assertTrue(Time::fromString('03:05:43') <= Time::fromString('13:05:42'));
    }
}