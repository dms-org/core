<?php

namespace Dms\Core\Tests\Common\Crud\Action\Object\Mapping;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Object\Mapping\ObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\Mapping\ObjectFormObjectMapping;
use Dms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Dms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;
use Dms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestFormObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectFormObjectMappingTest extends ObjectActionFormMappingTest
{

    /**
     * @return ObjectActionFormMapping
     */
    protected function objectFormMapping()
    {
        return new ObjectFormObjectMapping(new TestFormObject(TestEntity::getTestCollection()));
    }

    /**
     * @return string
     */
    protected function expectedDataDtoType()
    {
        return TestFormObject::class;
    }

    /**
     * @return TestFormObject
     */
    protected function expectedForm()
    {
        return new TestFormObject(TestEntity::getTestCollection());
    }

    /**
     * @return array[]
     */
    public function mappingTests()
    {
        return [
                [
                        [IObjectAction::OBJECT_FIELD_NAME => 1, 'string' => 'abc'],
                        new ObjectActionParameter(
                                TestEntity::withId(1),
                                $this->expectedForm()->submit([IObjectAction::OBJECT_FIELD_NAME => 1, 'string' => 'abc'])
                        )
                ],
                [
                        [IObjectAction::OBJECT_FIELD_NAME => 3, 'string' => 123],
                        new ObjectActionParameter(
                                TestEntity::withId(3),
                                $this->expectedForm()->submit([IObjectAction::OBJECT_FIELD_NAME => 3, 'string' => 123])
                        )
                ],
        ];
    }

    public function testWithSubmittedFirstStageWithMultipleStages()
    {
        $mapping = $this->mapping
                ->withSubmittedFirstStage([IObjectAction::OBJECT_FIELD_NAME => TestEntity::withId(1)]);

        $expected = new ObjectActionParameter(
                TestEntity::withId(1),
                $this->expectedForm()->submit([IObjectAction::OBJECT_FIELD_NAME => 1, 'string' => 'abc'])
        );
        $actual = $mapping->mapFormSubmissionToDto(['string' => 'abc']);

        $this->assertEquals($expected->getObject(), $actual->getObject());
        $this->assertSame(['object', 'string', 'dataSource'], array_keys($expected->getData()->toArray()));
        $this->assertEquals($expected->getData()->toArray(), $actual->getData()->toArray());
    }
}