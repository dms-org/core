<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler;

use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Module\Handler\CustomParameterizedActionHandler;
use Iddigital\Cms\Core\Module\InvalidHandlerClassException;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\ParamDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomMethodActionHandlerTest extends ParameterizedActionHandlerTest
{

    /**
     * @return IParameterizedActionHandler
     */
    protected function handler()
    {
        return new CustomParameterizedActionHandler(function (ParamDto $dto) {
            return ParamDto::from('foo');
        }, ParamDto::class);
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
        return ParamDto::from('bar');
    }

    protected function expectedReturnValue()
    {
        return ParamDto::from('foo');
    }


    public function testNoParameterMethodThrows()
    {
        $this->setExpectedException(InvalidHandlerClassException::class);
        new CustomParameterizedActionHandler(function () {
            return ParamDto::from('foo');
        });
    }

    public function testNoTypeHintMethodThrows()
    {
        $this->setExpectedException(InvalidHandlerClassException::class);
        new CustomParameterizedActionHandler(function ($foo) {
            return ParamDto::from('foo');
        });
    }

    public function testInvalidTypeHintMethodThrows()
    {
        $this->setExpectedException(InvalidHandlerClassException::class);
        new CustomParameterizedActionHandler(function (\DateTime $data) {
            return ParamDto::from('foo');
        });
    }
}