<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ObjectFormObjectMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestObjectForm;

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
        return new ObjectFormObjectMapping(new TestObjectForm(TestEntity::getTestCollection()));
    }

    /**
     * @return string
     */
    protected function expectedDataDtoType()
    {
        return TestObjectForm::class;
    }

    /**
     * @return TestObjectForm
     */
    protected function expectedForm()
    {
        return new TestObjectForm(TestEntity::getTestCollection());
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
                        [IObjectAction::OBJECT_FIELD_NAME => 3, 'field' => 123],
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