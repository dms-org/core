<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Crud;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\Mapping\WrapperObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\SelfHandlingObjectAction;
use Dms\Core\Common\Crud\Definition\Form\FinalizedCrudFormDefinition;
use Dms\Core\Common\Crud\Form\ObjectForm;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Builder\StagedForm;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\ITypedObject;

/**
 * The view details object action
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ViewDetailsAction extends SelfHandlingObjectAction
{
    /**
     * @var IIdentifiableObjectSet
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
        IIdentifiableObjectSet $dataSource,
        IAuthSystemInPackageContext $auth,
        FinalizedCrudFormDefinition $form
    )
    {
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
    protected function formMapping() : IObjectActionFormMapping
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
        return IStagedForm::class;
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
     * @return IStagedForm
     */
    protected function runHandler($object, $data = null)
    {
        /** @var ITypedObject $object */

        $stagedForm = $this->form->getStagedForm();
        $knownData  = [IObjectAction::OBJECT_FIELD_NAME => $object];
        $stages     = $stagedForm->withSubmittedFirstStage($knownData);

        $form = StagedForm::begin(ObjectForm::build($this->dataSource)->withInitialValues($knownData));

        foreach ($stages->getAllStages() as $stage) {
            $currentStageForm = $stage->loadForm($knownData);

            $form->then($currentStageForm);
            $knownData += $currentStageForm->getInitialValues();
        }

        return $form->build();
    }
}