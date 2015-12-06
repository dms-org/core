<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Crud;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\SelfHandlingObjectAction;
use Iddigital\Cms\Core\Common\Crud\Definition\Form\FinalizedCrudFormDefinition;
use Iddigital\Cms\Core\Common\Crud\ICrudModule;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The edit object action
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EditAction extends SelfHandlingObjectAction
{
    /**
     * @var IRepository
     */
    protected $dataSource;

    /**
     * @var FinalizedCrudFormDefinition
     */
    protected $form;

    /**
     * @inheritDoc
     */
    public function __construct(
            IRepository $dataSource,
            IAuthSystem $auth,
            FinalizedCrudFormDefinition $form
    ) {
        $this->dataSource = $dataSource;
        $this->form       = $form;

        parent::__construct($auth);
    }


    /**
     * Gets the action name.
     *
     * @return string
     */
    protected function name()
    {
        return ICrudModule::EDIT_ACTION;
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions()
    {
        return [
                Permission::named(ICrudModule::VIEW_PERMISSION),
                Permission::named(ICrudModule::EDIT_PERMISSION),
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
                $this->form->getStagedForm()
        );
    }

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    protected function returnType()
    {
        return $this->dataSource->getObjectType();
    }

    /**
     * Gets the object type
     *
     * @return string
     */
    protected function objectType()
    {
        return $this->dataSource->getObjectType();
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
        /** @var IEntity $object */
        /** @var ArrayDataObject $data */
        $input = $data->getArray();

        $this->form->bindToObject($object, $input);
        $this->form->invokeOnSubmitCallbacks($object, $input);

        $this->dataSource->save($object);

        $this->form->invokeOnSaveCallbacks($object, $input);

        return $object;
    }
}