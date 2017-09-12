<?php

namespace Dms\Core\Tests\Common\Crud\Action\Object;

use Dms\Core\Common\Crud\Action\Object\CustomObjectActionHandler;
use Dms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Module\IParameterizedActionHandler;
use Dms\Core\Tests\Common\Crud\Action\Object\Fixtures\ParamDto;
use Dms\Core\Tests\Common\Crud\Action\Object\Fixtures\ReturnDto;
use Dms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;

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
        $this->expectException(TypeMismatchException::class);

        $this->handler->runOnObject(ParamDto::from(1), ParamDto::from(1));
    }

    public function testInvalidParameterType()
    {
        $this->expectException(TypeMismatchException::class);

        $this->handler->runOnObject(TestEntity::withId(1), TestEntity::withId(1));
    }
}