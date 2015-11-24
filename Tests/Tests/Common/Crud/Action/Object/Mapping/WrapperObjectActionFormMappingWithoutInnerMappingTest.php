<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\WrapperObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectForm;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WrapperObjectActionFormMappingWithoutInnerMappingTest extends ObjectActionFormMappingTest
{

    /**
     * @return ObjectActionFormMapping
     */
    protected function objectFormMapping()
    {
        return new WrapperObjectActionFormMapping(ObjectForm::build(TestEntity::getTestCollection()));
    }

    /**
     * @return string
     */
    protected function expectedDataDtoType()
    {
        return null;
    }

    /**
     * @return IStagedForm
     */
    protected function expectedForm()
    {
        return ObjectForm::build(TestEntity::getTestCollection())->asStagedForm();
    }

    /**
     * @return array[]
     */
    public function mappingTests()
    {
        return [
                [
                        [IObjectAction::OBJECT_FIELD_NAME => 1],
                        new ObjectActionParameter(TestEntity::withId(1)),
                ],
                [
                        [IObjectAction::OBJECT_FIELD_NAME => 3],
                        new ObjectActionParameter(TestEntity::withId(3)),
                ],
        ];
    }
}