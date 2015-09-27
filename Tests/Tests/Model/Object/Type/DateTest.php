<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Type;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\Type\Date;
use Iddigital\Cms\Core\Model\Object\Type\Time;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTest extends DateOrTimeObjectTest
{
    public function testNew()
    {
        $date = new Date(2015, 03, 05);

        $this->assertInstanceOf(\DateTimeImmutable::class, $date->getNativeDateTime());
        $this->assertSame('2015-03-05 00:00:00', $date->getNativeDateTime()->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $date->getNativeDateTime()->getTimezone()->getName());
        $this->assertSame(2015, $date->getYear());
        $this->assertSame(3, $date->getMonth());
        $this->assertSame(5, $date->getDay());
        $this->assertSame('2015-03-05', $date->format('Y-m-d'));
        $this->assertSame(true, $date->equals($date));
        $this->assertSame(true, $date->equals(clone $date));
    }

    public function testFromNativeObject()
    {
        $date = Date::fromNative(new \DateTime('2001-05-1'));
        $this->assertSame(2001, $date->getYear());
        $this->assertSame(5, $date->getMonth());
        $this->assertSame(1, $date->getDay());
    }

    public function testFromFormat()
    {
        $date = Date::fromFormat('d/m/Y', '21/8/2001');

        $this->assertSame(2001, $date->getYear());
        $this->assertSame(8, $date->getMonth());
        $this->assertSame(21, $date->getDay());
    }

    public function testAddingAndSubtracting()
    {
        $date = new Date(2000, 1, 1);

        $otherTime = $date->addYears(10)->subMonths(3)->addDays(20);

        $this->assertNotSame($date, $otherTime);
        $this->assertSame('2000-01-01', $date->format('Y-m-d'));
        $this->assertSame('2009-10-21', $otherTime->format('Y-m-d'));
    }

    public function testComparisons()
    {
        $date = new Date(2000, 1, 1);

        $this->assertFalse($date->comesBefore($date));
        $this->assertFalse($date->comesBefore($date->subDays(1)));
        $this->assertTrue($date->comesBefore($date->addDays(1)));

        $this->assertTrue($date->comesBeforeOrEqual($date));
        $this->assertFalse($date->comesBeforeOrEqual($date->subDays(1)));
        $this->assertTrue($date->comesBeforeOrEqual($date->addDays(1)));

        $this->assertFalse($date->comesAfter($date));
        $this->assertFalse($date->comesAfter($date->addDays(1)));
        $this->assertTrue($date->comesAfter($date->subDays(1)));

        $this->assertTrue($date->comesAfterOrEqual($date));
        $this->assertFalse($date->comesAfterOrEqual($date->addDays(1)));
        $this->assertTrue($date->comesAfterOrEqual($date->subDays(1)));
    }

    public function testBetween()
    {
        $date = new Date(2000, 1, 1);

        $this->assertTrue($date->isBetween($date->subDays(1), $date->addDays(1)));
        $this->assertFalse($date->isBetween($date, $date->addDays(1)));
        $this->assertFalse($date->isBetween($date->subDays(1), $date));
        $this->assertFalse($date->isBetween($date, $date));
        $this->assertFalse($date->isBetween($date->addDays(1), $date->addDays(2)));

        $this->assertTrue($date->isBetweenInclusive($date->subDays(1), $date->addDays(1)));
        $this->assertTrue($date->isBetweenInclusive($date, $date->addDays(1)));
        $this->assertTrue($date->isBetweenInclusive($date->subDays(1), $date));
        $this->assertTrue($date->isBetweenInclusive($date, $date));
        $this->assertFalse($date->isBetweenInclusive($date->addDays(1), $date->addDays(2)));

        $this->assertThrows(function () use ($date) {
            $date->isBetween($date->addDays(1), $date);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($date) {
            $date->isBetweenInclusive($date->addDays(1), $date);
        }, InvalidArgumentException::class);
    }
}