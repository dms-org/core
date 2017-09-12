<?php

namespace Dms\Core\Tests\Module\Handler;

use Dms\Core\Model\IEntity;
use Dms\Core\Module\InvalidHandlerClassException;
use Dms\Core\Module\IParameterizedActionHandler;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Model\Object\DataTransferObject;
use Dms\Core\Tests\Module\Handler\Fixtures\EntityTypeHintMethodActionHandler;
use Dms\Core\Tests\Module\Handler\Fixtures\NoHandleMethodActionHandler;
use Dms\Core\Tests\Module\Handler\Fixtures\NoParameterMethodActionHandler;
use Dms\Core\Tests\Module\Handler\Fixtures\NoTypeHintMethodActionHandler;
use Dms\Core\Tests\Module\Handler\Fixtures\ParamDto;
use Dms\Core\Tests\Module\Handler\Fixtures\ParameterizedMethodActionHandler;
use Dms\Core\Tests\Module\Handler\Fixtures\PrivateMethodActionHandler;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParameterizedMethodActionHandlerTest extends ParameterizedActionHandlerTest
{

    /**
     * @return IParameterizedActionHandler
     */
    protected function handler()
    {
        return new ParameterizedMethodActionHandler();
    }

    protected function expectedDtoType()
    {
        return ParamDto::class;
    }

    /**
     * @return IDataTransferObject
     */
    protected function runParameter()
    {
        return ParamDto::from(100);
    }

    protected function expectedReturnValue()
    {
        return ParamDto::from(123);
    }

    public function testNoHandleMethodThrows()
    {
        $this->expectException(InvalidHandlerClassException::class);
        new NoHandleMethodActionHandler();
    }

    public function testPrivateHandleMethodThrows()
    {
        $this->expectException(InvalidHandlerClassException::class);
        new PrivateMethodActionHandler();
    }

    public function testNoParameterMethodThrows()
    {
        $this->expectException(InvalidHandlerClassException::class);
        new NoParameterMethodActionHandler();
    }

    public function testNoTypeHintMethodThrows()
    {
        $this->expectException(InvalidHandlerClassException::class);
        new NoTypeHintMethodActionHandler();
    }

    public function testNonDtoParameterAndReturnType()
    {
        $action = new EntityTypeHintMethodActionHandler();
        $this->assertSame(IEntity::class, $action->getParameterTypeClass());
        $this->assertSame(IEntity::class, $action->getReturnTypeClass());
    }
}