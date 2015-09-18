<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler;

use Iddigital\Cms\Core\Module\InvalidHandlerClassException;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\Object\DataTransferObject;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\InvalidTypeHintMethodActionHandler;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\NoHandleMethodActionHandler;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\NoParameterMethodActionHandler;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\NoTypeHintMethodActionHandler;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\ParamDto;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\ParameterizedMethodActionHandler;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\PrivateMethodActionHandler;

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
        $this->setExpectedException(InvalidHandlerClassException::class);
        new NoHandleMethodActionHandler();
    }

    public function testPrivateHandleMethodThrows()
    {
        $this->setExpectedException(InvalidHandlerClassException::class);
        new PrivateMethodActionHandler();
    }

    public function testNoParameterMethodThrows()
    {
        $this->setExpectedException(InvalidHandlerClassException::class);
        new NoParameterMethodActionHandler();
    }

    public function testNoTypeHintMethodThrows()
    {
        $this->setExpectedException(InvalidHandlerClassException::class);
        new NoTypeHintMethodActionHandler();
    }

    public function testInvalidTypeHintMethodThrows()
    {
        $this->setExpectedException(InvalidHandlerClassException::class);
        new InvalidTypeHintMethodActionHandler();
    }
}