<?php

namespace Dms\Core\Tests\Common\Crud\Action\Object\Fixtures;

use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\SelfHandlingObjectAction;
use Dms\Core\Common\Crud\Form\ObjectForm;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Builder\StagedForm;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestSelfHandlingObjectAction extends SelfHandlingObjectAction
{


    /**
     * Gets the action name.
     *
     * @return string
     */
    protected function name() : string
    {
        return 'test-object-action';
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions() : array
    {
        return [
            Permission::named('test-permission')
        ];
    }

    /**
     * Gets the action form mapping.
     *
     * @return IObjectActionFormMapping
     */
    protected function formMapping() : IObjectActionFormMapping
    {
        return new ArrayObjectActionFormMapping(
            StagedForm::begin(
                    ObjectForm::build(TestEntity::getTestCollection())
            )->then(
                Form::create()->section('Input', [
                        Field::name('string')->label('String')->string()->required()
                ])
            )->build()
        );
    }

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    protected function returnType()
    {
        return ReturnDto::class;
    }

    /**
     * Gets the object type
     *
     * @return string
     */
    protected function objectType() : string
    {
        return TestEntity::class;
    }

    /**
     * Runs the action handler.
     *
     * @param object      $object
     * @param object|null $data
     *
     * @return object|null
     */
    protected function runHandler($object, $data = null)
    {
        /** @var TestEntity $object */
        /** @var ArrayDataObject $data */
        return ReturnDto::from($object->getId() . '_' . $data['string']);
    }
}