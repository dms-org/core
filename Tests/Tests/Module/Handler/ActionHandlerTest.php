<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Module\IUnparameterizedActionHandler;

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
    protected function setUp()
    {
        $this->handler = $this->handler();
    }

    abstract protected function expectedReturnValue();

    public function testRunningHandler()
    {
        $this->assertEquals($this->expectedReturnValue(), $this->handler->run());
    }
}