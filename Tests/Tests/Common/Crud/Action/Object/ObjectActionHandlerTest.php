<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionHandler;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Tests\Module\Handler\ParameterizedActionHandlerTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectActionHandlerTest extends ParameterizedActionHandlerTest
{
    /**
     * @var ObjectActionHandler
     */
    protected $handler;

    protected function expectedDtoType()
    {
        return ObjectActionParameter::class;
    }

    /**
     * @return string|null
     */
    abstract protected function expectedDataDtoType();



    public function testDataDtoType()
    {
        $this->assertSame($this->expectedDataDtoType() !== null, $this->handler->hasDataDtoType());
        $this->assertSame($this->expectedDataDtoType(), $this->handler->getDataDtoType());
    }

    public function testRunningHandlerViaRunOnObject()
    {
        /** @var ObjectActionParameter $runParam */
        $runParam = $this->runParameter();
        $this->assertEquals($this->expectedReturnValue(), $this->handler->runOnObject($runParam->getObject(), $runParam->getData()));
    }
}