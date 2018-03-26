<?php declare(strict_types=1);

namespace Dms\Core\Common\Crud\Action\Crud;

use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\SelfHandlingObjectAction;
use Dms\Core\Common\Crud\Definition\Form\FinalizedCrudFormDefinition;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\IValueObject;
use Dms\Core\Model\IValueObjectCollection;
use Dms\Core\Model\Object\ArrayDataObject;

/**
 * The edit object action
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EditAction extends SelfHandlingObjectAction
{
    /**
     * @var IMutableObjectSet
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
        IMutableObjectSet $dataSource,
        IAuthSystemInPackageContext $auth,
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
    protected function name(): string
    {
        return ICrudModule::EDIT_ACTION;
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions(): array
    {
        return [
            Permission::named(ICrudModule::VIEW_PERMISSION),
            Permission::named(ICrudModule::EDIT_PERMISSION),
        ];
    }

    /**
     * Gets the action metadata.
     *
     * @return array
     */
    protected function metadata(): array
    {
        return $this->form->getMetadata() + [
            'submit-button-text' => 'Save',
        ];
    }

    /**
     * Gets the action form mapping.
     *
     * @return IObjectActionFormMapping
     */
    protected function formMapping(): IObjectActionFormMapping
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
    protected function objectType(): string
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
        /** @var ITypedObject $object */
        /** @var ArrayDataObject $data */
        $input = $data->getArray();

        if ($this->dataSource instanceof IValueObjectCollection) {
            /** @var IValueObject $object */
            /** @var IValueObject $newObject */
            $newObject = $this->form->createNewObjectFromInput($input);

            $this->form->invokeBeforeSubmitCallbacks($newObject, $input);
            $this->form->bindToObject($newObject, $input);
            $this->form->invokeOnSubmitCallbacks($newObject, $input);

            $this->dataSource->update($object, $newObject);

            $this->form->invokeOnSaveCallbacks($object, $input);

            return $newObject;
        } else {
            $this->form->invokeBeforeSubmitCallbacks($object, $input);
            $this->form->bindToObject($object, $input);
            $this->form->invokeOnSubmitCallbacks($object, $input);

            $this->dataSource->save($object);

            $this->form->invokeOnSaveCallbacks($object, $input);

            return $object;
        }
    }
}