<?php

namespace Dms\Core\Tests\Module\Handler;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Module\IParameterizedActionHandler;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ParameterizedActionHandlerTest extends CmsTestCase
{
    /**
     * @var IParameterizedActionHandler
     */
    protected $handler;

    /**
     * @return IParameterizedActionHandler
     */
    abstract protected function handler();

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->handler = $this->handler();
    }

    /**
     * @return IDataTransferObject
     */
    abstract protected function runParameter();

    abstract protected function expectedReturnValue();

    abstract protected function expectedDtoType();

    public function testDtoType()
    {
        $this->assertEquals($this->expectedDtoType(), $this->handler->getParameterTypeClass());
    }

    public function testReturnType()
    {
        $expectedReturnValue = $this->expectedReturnValue();

        if ($expectedReturnValue === null) {
            $this->assertNull($this->handler->getReturnTypeClass());
        } else {
            $this->assertSame(get_class($expectedReturnValue), $this->handler->getReturnTypeClass());
        }
    }

    public function testRunningHandler()
    {
        $this->assertEquals($this->expectedReturnValue(), $this->handler->run($this->runParameter()));
    }
}