<?php

namespace Dms\Core\Tests\Module\Handler;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Module\IUnparameterizedActionHandler;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ActionHandlerTest extends CmsTestCase
{
    /**
     * @var IUnparameterizedActionHandler
     */
    protected $handler;

    /**
     * @return IUnparameterizedActionHandler
     */
    abstract protected function handler();

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->handler = $this->handler();
    }

    abstract protected function expectedReturnValue();

    public function testRunningHandler()
    {
        $this->assertEquals($this->expectedReturnValue(), $this->handler->run());
    }
}