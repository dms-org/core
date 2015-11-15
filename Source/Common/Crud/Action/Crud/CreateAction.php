<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Crud;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Common\Crud\Definition\Form\FinalizedCrudFormDefinition;
use Iddigital\Cms\Core\Common\Crud\ICrudModule;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Module\Action\SelfHandlingParameterizedAction;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\Mapping\ArrayDataObjectFormMapping;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The create object action
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CreateAction extends SelfHandlingParameterizedAction
{
    /**
     * @var IRepository
     */
    private $dataSource;

    /**
     * @var FinalizedCrudFormDefinition
     */
    private $form;

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
        return ICrudModule::CREATE_ACTION;
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
                Permission::named(ICrudModule::CREATE_PERMISSION)
        ];
    }

    /**
     * Gets the action form mapping.
     *
     * @return IStagedFormDtoMapping
     */
    protected function formMapping()
    {
        return new ArrayDataObjectFormMapping(
                $this->form->getStagedForm()
        );
    }

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    protected function returnDtoType()
    {
        return $this->dataSource->getObjectType();
    }

    /**
     * Runs the action handler.
     *
     * @param IDataTransferObject $data
     *
     * @return IDataTransferObject|null
     */
    protected function runHandler(IDataTransferObject $data)
    {
        /** @var ArrayDataObject $data */
        $input       = $data->getArray();
        $constructor = $this->form->getCreateObjectCallback();
        $newObject   = $constructor($input);

        $this->form->bindToObject($newObject, $input);
        $this->form->invokeOnSubmitCallbacks($newObject, $input);

        $this->dataSource->save($newObject);

        $this->form->invokeOnSaveCallbacks($newObject, $input);

        return $newObject;
    }
}