<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Crud;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\WrapperObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\SelfHandlingObjectAction;
use Iddigital\Cms\Core\Common\Crud\Definition\Form\FinalizedCrudFormDefinition;
use Iddigital\Cms\Core\Common\Crud\Form\FormWithBinding;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectForm;
use Iddigital\Cms\Core\Common\Crud\IReadModule;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IEntitySet;

/**
 * The view details object action
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ViewDetailsAction extends SelfHandlingObjectAction
{
    /**
     * @var IEntitySet
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
            IEntitySet $dataSource,
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
        return IReadModule::DETAILS_ACTION;
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions()
    {
        return [
                Permission::named(IReadModule::VIEW_PERMISSION),
        ];
    }

    /**
     * Gets the action form mapping.
     *
     * @return IObjectActionFormMapping
     */
    protected function formMapping()
    {
        return new WrapperObjectActionFormMapping(
                ObjectForm::build($this->dataSource)
        );
    }

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    protected function returnType()
    {
        return IForm::class;
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
     * @return IForm
     */
    protected function runHandler($object, $data = null)
    {
        /** @var IEntity $object */

        $stagedForm = $this->form->getStagedForm();
        $knownData = [IObjectAction::OBJECT_FIELD_NAME => $object];
        $stages    = $stagedForm->withSubmittedFirstStage($knownData);

        $form = Form::create();
        $form->embed($stagedForm->getFirstForm()->withInitialValues($knownData));

        foreach ($stages->getAllStages() as $stage) {
            /** @var FormWithBinding $currentStageForm */
            $currentStageForm = $stage->loadForm($knownData);
            $currentStageForm = $currentStageForm->getBinding()->getForm($object);

            $form->embed($currentStageForm);
            $knownData += $currentStageForm->getInitialValues();
        }

        return $form->build();
    }
}