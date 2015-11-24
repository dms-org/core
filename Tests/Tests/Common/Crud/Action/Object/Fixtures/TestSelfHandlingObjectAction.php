<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Fixtures;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\SelfHandlingObjectAction;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectForm;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;

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
    protected function name()
    {
        return 'test-object-action';
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions()
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
    protected function formMapping()
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
    protected function objectType()
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