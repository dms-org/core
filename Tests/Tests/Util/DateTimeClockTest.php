<?php

namespace Iddigital\Cms\Core\Tests\Util;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Util\DateTimeClock;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeClockTest extends CmsTestCase
{
    /**
     * @var DateTimeClock
     */
    protected $clock;

    public function setUp()
    {
        $this->clock = new DateTimeClock();
    }

    public function testGetNow()
    {
        $now = $this->clock->now();

        $this->assertInstanceOf(\DateTimeImmutable::class, $now);
        $this->assertNotSame($now, $this->clock->now());
        $this->assertSame(date_default_timezone_get(), $this->clock->now()->getTimezone()->getName());
    }

    public function testGetUtcNow()
    {
        $now = $this->clock->utcNow();

        $this->assertInstanceOf(\DateTimeImmutable::class, $now);
        $this->assertNotSame($now, $this->clock->utcNow());
        $this->assertSame('UTC', $this->clock->now()->getTimezone()->getName());
    }
}