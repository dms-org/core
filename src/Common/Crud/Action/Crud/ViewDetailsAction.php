<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Crud;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\Mapping\WrapperObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\SelfHandlingObjectAction;
use Dms\Core\Common\Crud\Definition\Form\FinalizedCrudFormDefinition;
use Dms\Core\Common\Crud\Form\FormWithBinding;
use Dms\Core\Common\Crud\Form\ObjectForm;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\IForm;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\IEntitySet;

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
    protected $dataSource;

    /**
     * @var FinalizedCrudFormDefinition
     */
    protected $form;

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
    protected function name() : string
    {
        return IReadModule::DETAILS_ACTION;
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions() : array
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
    protected function formMapping() : \Dms\Core\Common\Crud\Action\Object\IObjectActionFormMapping
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
    protected function objectType() : string
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
            $currentStageForm = $stage->loadForm($knownData);

            $form->embed($currentStageForm);
            $knownData += $currentStageForm->getInitialValues();
        }

        return $form->build();
    }
}