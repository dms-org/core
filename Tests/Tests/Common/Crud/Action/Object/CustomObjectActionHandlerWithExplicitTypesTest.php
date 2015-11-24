<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Common\Crud\Action\Object\CustomObjectActionHandler;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Fixtures\ParamDto;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Fixtures\ReturnDto;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomObjectActionHandlerWithExplicitTypeseTest extends ObjectActionHandlerTest
{
    /**
     * @return IParameterizedActionHandler
     */
    protected function handler()
    {
        return new CustomObjectActionHandler(
                function ($entity, $paramDto) {
                    return ReturnDto::from($entity->getId() . '-' . $paramDto->value . '-foo');
                },
                ReturnDto::class,
                TestEntity::class,
                ParamDto::class
        );
    }

    /**
     * @return string|null
     */
    protected function expectedDataDtoType()
    {
        return ParamDto::class;
    }

    /**
     * @return IDataTransferObject
     */
    protected function runParameter()
    {
        return new ObjectActionParameter(
                TestEntity::withId(3),
                ParamDto::from('abc')
        );
    }

    protected function expectedReturnValue()
    {
        return ReturnDto::from('3-abc-foo');
    }

    public function testInvalidObjectType()
    {
        $this->setExpectedException(TypeMismatchException::class);

        $this->handler->runOnObject(ParamDto::from(1), ParamDto::from(1));
    }

    public function testInvalidParameterType()
    {
        $this->setExpectedException(TypeMismatchException::class);

        $this->handler->runOnObject(TestEntity::withId(1), TestEntity::withId(1));
    }
}