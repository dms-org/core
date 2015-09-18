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

        $this->assertInstanceOf(\DateTime::class, $now);
        $this->assertNotSame($now, $this->clock->now());
    }
}