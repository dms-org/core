<?php

namespace Dms\Core\Tests\Util;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Util\DateTimeClock;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeClockTest extends CmsTestCase
{
    /**
     * @var DateTimeClock
     */
    protected $clock;

    public function setUp(): void
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