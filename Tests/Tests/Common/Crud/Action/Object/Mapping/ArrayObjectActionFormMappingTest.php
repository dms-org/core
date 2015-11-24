<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectForm;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayObjectActionFormMappingTest extends ObjectActionFormMappingTest
{

    /**
     * @return ObjectActionFormMapping
     */
    protected function objectFormMapping()
    {
        return new ArrayObjectActionFormMapping(
                $this->expectedForm()
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
                                Field::create()->name('field')->label('Field')->string()
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
                        [IObjectAction::OBJECT_FIELD_NAME => 1, 'field' => 'abc'],
                        new ObjectActionParameter(
                                TestEntity::withId(1),
                                new ArrayDataObject([IObjectAction::OBJECT_FIELD_NAME => TestEntity::withId(1), 'field' => 'abc'])
                        )
                ],
                [
                        [IObjectAction::OBJECT_FIELD_NAME => 3, 'field' => 123],
                        new ObjectActionParameter(
                                TestEntity::withId(3),
                                new ArrayDataObject([IObjectAction::OBJECT_FIELD_NAME => TestEntity::withId(3), 'field' => '123'])
                        )
                ],
        ];
    }
}