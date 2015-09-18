<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Module\IUnparameterizedActionHandler;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\Object\DataTransferObject;

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
        $this->assertEquals($this->expectedDtoType(), $this->handler->getDtoType());
    }

    public function testReturnType()
    {
        $expectedReturnValue = $this->expectedReturnValue();

        if ($expectedReturnValue === null) {
            $this->assertNull($this->handler->getReturnDtoType());
        } else {
            $this->assertSame(get_class($expectedReturnValue), $this->handler->getReturnDtoType());
        }
    }

    public function testRunningHandler()
    {
        $this->assertEquals($this->expectedReturnValue(), $this->handler->run($this->runParameter()));
    }
}