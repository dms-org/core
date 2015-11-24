<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\WrapperObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectForm;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Module\Mapping\ArrayDataObjectFormMapping;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WrapperObjectActionFormMappingWithInnerMappingTest extends ObjectActionFormMappingTest
{

    /**
     * @return ObjectActionFormMapping
     */
    protected function objectFormMapping()
    {
        return new WrapperObjectActionFormMapping(
                ObjectForm::build(TestEntity::getTestCollection()),
                new ArrayDataObjectFormMapping(
                        Form::create()
                                ->section('Test', [
                                        Field::name('field')->label('Field')->bool()
                                ])
                                ->build()->asStagedForm()
                )
        );
    }

    /**
     * @return string
     */
    protected function expectedDataDtoType()
    {
        return ArrayDataObject::class;
    }

    /**
     * @return IStagedForm
     */
    protected function expectedForm()
    {
        return StagedForm::begin(
                ObjectForm::build(TestEntity::getTestCollection())
        )->then(
                Form::create()
                        ->section('Test', [
                                Field::name('field')->label('Field')->bool()
                        ])
                        ->build()
        )->build();
    }

    /**
     * @return array[]
     */
    public function mappingTests()
    {
        return [
                [
                        [IObjectAction::OBJECT_FIELD_NAME => 1, 'field' => '0'],
                        new ObjectActionParameter(
                                TestEntity::withId(1),
                                new ArrayDataObject(['field' => false])
                        ),
                ],
                [
                        [IObjectAction::OBJECT_FIELD_NAME => 3, 'field' => '1'],
                        new ObjectActionParameter(
                                TestEntity::withId(3),
                                new ArrayDataObject(['field' => true])
                        ),
                ],
        ];
    }

    public function testWithSubmittedFirstStageWithMultipleStages()
    {
        $mapping = new WrapperObjectActionFormMapping(
                ObjectForm::build(TestEntity::getTestCollection()),
                new ArrayDataObjectFormMapping(
                    StagedForm::begin(
                        Form::create()
                                ->section('Test', [
                                        Field::name('field')->label('Field')->bool()
                                ])
                    )->then(
                            Form::create()
                                    ->section('Test 2', [
                                            Field::name('second')->label('Second')->string()
                                    ])
                    )->build()
                )
        );

        $mapping = $mapping
                ->withSubmittedFirstStage([IObjectAction::OBJECT_FIELD_NAME => TestEntity::withId(2)])
                ->withSubmittedFirstStage(['field' => true]);

        $expected = new ObjectActionParameter(
                TestEntity::withId(2),
                new ArrayDataObject(['field' => true, 'second' => 'abc'])
        );
        $actual = $mapping->mapFormSubmissionToDto(['second' => 'abc']);

        $this->assertEquals($expected, $actual);
    }
}