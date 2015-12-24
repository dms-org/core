<?php

namespace Dms\Core\Common\Crud\Definition\Form;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Form\FormWithBinding;
use Dms\Core\Common\Crud\Form\ObjectForm;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Exception\InvalidReturnValueException;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\FormSection;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFormSection;
use Dms\Core\Form\IFormStage;
use Dms\Core\Form\Stage\DependentFormStage;
use Dms\Core\Form\Stage\IndependentFormStage;
use Dms\Core\Form\StagedForm;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Util\Debug;
use Dms\Core\Util\Reflection;

/**
 * The CRUD form definition class.
 *
 * Provides a readable API for definition forms bound to
 * objects, for creation, viewing and updating.
 *
 * This constructs a staged form contains instances of {@see FormWithBinding}
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CrudFormDefinition
{
    const MODE_DETAILS = IReadModule::DETAILS_ACTION;
    const MODE_CREATE = ICrudModule::CREATE_ACTION;
    const MODE_EDIT = ICrudModule::EDIT_ACTION;

    protected static $modes = [self::MODE_DETAILS, self::MODE_CREATE, self::MODE_EDIT];

    /**
     * @var FinalizedClassDefinition
     */
    private $class;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var bool
     */
    protected $isDependent;

    /**
     * @var IFormStage[]
     */
    protected $stages = [];

    /**
     * @var IFormStage[]
     */
    protected $stageBindings = [];

    /**
     * @var IFormSection[]
     */
    protected $currentStageSections = [];

    /**
     * @var IFieldBinding[]
     */
    protected $currentStageFieldBindings = [];

    /**
     * @var callable
     */
    protected $createObjectCallback;

    /**
     * @var callable[]
     */
    protected $onSubmitCallbacks = [];

    /**
     * @var callable[]
     */
    protected $onSaveCallbacks = [];

    /**
     * @var string|null
     */
    protected $currentEditedObjectType;

    /**
     * CrudFormDefinition constructor.
     *
     * @param IEntitySet               $dataSource
     * @param FinalizedClassDefinition $class
     * @param string                   $mode
     * @param bool                     $isDependent
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IEntitySet $dataSource, FinalizedClassDefinition $class, $mode, $isDependent = false)
    {
        if (!in_array($mode, self::$modes, true)) {
            throw InvalidArgumentException::format(
                    'Mode must must be one of (%s), \'%s\' given',
                    Debug::formatValues(self::$modes), $mode
            );
        }

        $this->class       = $class;
        $this->mode        = $mode;
        $this->isDependent = $isDependent;

        if ($this->mode !== self::MODE_CREATE) {
            $this->stages[] = new IndependentFormStage(ObjectForm::build($dataSource));
        }

        // Throw exception inside callback as the class definition may have
        // changed via self::mapToSubClass() or this is an edit form and the
        // class will be updated automatically.
        $this->createObjectCallback = function () {
            if ($this->class->isAbstract()) {
                throw InvalidOperationException::format(
                        'Cannot instantiate object of type %s in crud form mode \'%s\': the class is abstract, did you forget to specify a subclass via %s?',
                        $this->class->getClassName(), $this->mode, '->createObjectType() or ->mapToSubClass()'
                );
            }

            return $this->class->newCleanInstance();
        };
    }

    /**
     * Returns whether this is the form definition for viewing
     * an object from the module data source.
     *
     * @return bool
     */
    public function isDetailsForm()
    {
        return $this->mode === self::MODE_DETAILS;
    }

    /**
     * Returns whether this is the form definition for creating
     * a new object and saving it to the module data source.
     *
     * @return bool
     */
    public function isCreateForm()
    {
        return $this->mode === self::MODE_CREATE;
    }

    /**
     * Returns whether this is the form definition for updating
     * an existing object and saving it to the module data source.
     *
     * @return bool
     */
    public function isEditForm()
    {
        return $this->mode === self::MODE_EDIT;
    }

    /**
     * Defines a form section with the supplied form field bindings.
     *
     * Standard fields can be passed if there is no binding.
     *
     * Example:
     * <code>
     * $form->section('Details', [
     *      $form->field(Field::name('name')->label('Name')->string()->required())
     *              ->bindToProperty('name'),
     *      Field::name('age')->label('Age')->int(), // Field without binding
     * ]);
     * </code>
     *
     * @param string                                                   $title
     * @param FormFieldBindingDefinition[]|IField[]|FieldBuilderBase[] $fieldBindings
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function section($title, array $fieldBindings)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'fieldBindings', $fieldBindings, FormFieldBindingDefinition::class);

        $fields = [];
        foreach ($fieldBindings as $fieldBinding) {
            if ($fieldBinding instanceof FormFieldBindingDefinition) {
                $fields[] = $fieldBinding->getField();

                if ($fieldBinding->hasBinding()) {
                    $this->currentStageFieldBindings[] = $fieldBinding->getBinding();
                }
            } elseif ($fieldBinding instanceof FieldBuilderBase) {
                $fields[] = $fieldBinding->build();
            } elseif ($fieldBinding instanceof IField) {
                $fields[] = $fieldBinding;
            } else {
                throw InvalidArgumentException::format(
                        'Invalid call to %s: parameter $fieldBindings must only contain instances of %s, %s found',
                        __METHOD__, implode('|', [FormFieldBindingDefinition::class, FieldBuilderBase::class, IField::class]),
                        Debug::getType($fieldBinding)
                );
            }
        }

        $this->currentStageSections[] = new FormSection($title, $fields);
    }

    /**
     * Defines a field in the current form section.
     *
     * @param IField|FieldBuilderBase $field
     *
     * @return FormFieldBindingDefiner
     */
    public function field($field)
    {
        if ($field instanceof FieldBuilderBase) {
            $field = $field->build();
        }

        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'field', $field, IField::class);

        return new FormFieldBindingDefiner($this->class, $field);
    }

    /**
     * Defines a section of the form that is dependent on other fields.
     *
     * The supplied callback will be passed the values for the dependent fields
     * as the second parameter and the object instance as the third parameter or
     * NULL if it is a create form.
     *
     * Example:
     * <code>
     * $form->dependentOn(['name'], function (CrudFormDefinition $form, array $input, Person $object = null) {
     *      if ($input['name'] === 'John') {
     *          // ...
     *      } else {
     *          // ...
     *      }
     * });
     * </code>
     *
     * @param string[] $previousFieldNames
     * @param callable $dependentStageDefineCallback
     * @param string[] $fieldNamesDefinedInStage
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function dependentOn(array $previousFieldNames, callable $dependentStageDefineCallback, array $fieldNamesDefinedInStage = [])
    {
        if ($this->isDependent) {
            throw InvalidOperationException::format(
                    'Invalid call to %s: cannot nest dependent form sections'
            );
        }

        $this->finishCurrentStage();

        // If the callback requires a third parameter, this will be entity instance
        // to which the form is bound to. If so, ensure that it is marked as dependent
        // on the object id field.
        if (Reflection::fromCallable($dependentStageDefineCallback)->getNumberOfParameters() > 2 && !$this->isCreateForm()) {
            $previousFieldNames[] = IObjectAction::OBJECT_FIELD_NAME;
        }

        $this->stages[] = new DependentFormStage(function (array $previousData) use ($dependentStageDefineCallback) {
            $this->isDependent = true;
            $objectInstance    = isset($previousData[IObjectAction::OBJECT_FIELD_NAME])
                    ? $previousData[IObjectAction::OBJECT_FIELD_NAME]
                    : null;

            if ($objectInstance) {
                /** @var TypedObject $objectInstance */
                $this->class                   = $objectInstance::definition();
                $this->currentEditedObjectType = get_class($objectInstance);
            }

            $dependentStageDefineCallback(
                    $this,
                    $previousData,
                    $objectInstance
            );
            $this->isDependent = false;

            $form = $this->buildFormForCurrentStage();
            $this->exitStage();

            return $form;
        }, $fieldNamesDefinedInStage, array_unique($previousFieldNames));
    }

    /**
     * Defines a section of the form that is dependent on the object which the form is bound to.
     *
     * The supplied callback will be passed the object instance as the second parameter.
     *
     * NOTE: This will ignore the fields defined in this section if it is a create form and
     * the object field is a required parameter, if you want to support this in create forms,
     * default the object parameter to null and handle this case.
     *
     * Example:
     * <code>
     * $form->dependentOnObject(function (CrudFormDefinition $form, Person $person = null) {
     *      if ($person === null) { // Equivalent to $form->isCreateForm()
     *          // ...
     *      } elseif ($person->isAdmin()) {
     *          // ...
     *      } else {
     *          // ...
     *      }
     * });
     * </code>
     *
     * @param callable $dependentStageDefineCallback
     * @param string[] $fieldNamesDefinedInStage
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function dependentOnObject(callable $dependentStageDefineCallback, array $fieldNamesDefinedInStage = [])
    {
        $requiredParameters = Reflection::fromCallable($dependentStageDefineCallback)->getNumberOfRequiredParameters();

        if ($this->isCreateForm()) {
            if ($requiredParameters === 1) {
                $dependentStageDefineCallback($this);
            }

            return;
        }

        $this->dependentOn([], function (CrudFormDefinition $definition, array $previousData, $object) use ($dependentStageDefineCallback) {
            $dependentStageDefineCallback($definition, $object);
        }, $fieldNamesDefinedInStage);
    }

    protected function finishCurrentStage()
    {
        if ($this->currentStageSections) {
            $this->stages[] = new IndependentFormStage($this->buildFormForCurrentStage());
            $this->exitStage();
        }
    }

    protected function buildFormForCurrentStage()
    {
        return new FormWithBinding(
                $this->currentStageSections,
                [],
                $this->class->getClassName(),
                $this->currentStageFieldBindings
        );
    }

    protected function exitStage()
    {
        $this->currentStageSections      = [];
        $this->currentStageFieldBindings = [];
    }

    /**
     * Defines that the form should map to an instance of the supplied type.
     *
     * @param string $classType
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function mapToSubClass($classType)
    {
        if ($this->isEditForm() && $this->currentEditedObjectType) {
            if ($this->currentEditedObjectType !== $classType) {
                throw InvalidArgumentException::format(
                        'Invalid class type supplied to %s: cannot map to subclass %s, as the current object being edited is of type %s',
                        __METHOD__, $classType, $this->currentEditedObjectType
                );
            }
        }

        if (!is_a($classType, $this->class->getClassName(), true)) {
            throw InvalidArgumentException::format(
                    'Invalid class type supplied to %s: expecting subclass of %s, %s given',
                    __METHOD__, $this->class->getClassName(), $classType
            );
        }

        /** @var string|TypedObject $classType */
        $this->class = $classType::definition();
        $this->createObjectType()->asClass($classType);
    }

    /**
     * Defines a callback to create new instances of the object.
     * The callback can either return an instance or the class
     * name of the object of which to construct.
     *
     * @return ObjectConstructorCallbackDefiner
     */
    public function createObjectType()
    {
        return new ObjectConstructorCallbackDefiner($this->class, function (callable $typeCallback) {
            $this->createObjectCallback = function (array $input) use ($typeCallback) {
                $className = $this->class->getClassName();

                /** @var TypedObject|string $instanceOrType */
                $instanceOrType = $typeCallback($input);

                if (is_string($instanceOrType)) {
                    if (class_exists($instanceOrType) && is_a($instanceOrType, $className, true)) {
                        $instanceOrType = $instanceOrType::definition()->newCleanInstance();
                    }
                }

                if (!($instanceOrType instanceof $className)) {
                    throw InvalidReturnValueException::format(
                            'Invalid create object callback return value: expecting class compatible with %s, %s given',
                            $className, is_string($instanceOrType) ? $instanceOrType : Debug::getType($instanceOrType)
                    );
                }

                return $instanceOrType;
            };
        });
    }

    /**
     * Defines an form submission callback.
     *
     * This will be executed when the form is submitted
     * after the form data has been bound to the object.
     *
     * This will NOT be called on a details form.
     *
     * Example:
     * <code>
     * $form->onSubmit(function (Person $object, array $input) {
     *      $object->doSomething($input['data']);
     * });
     * </code>
     *
     * @param callable $callback
     *
     * @return void
     */
    public function onSubmit(callable $callback)
    {
        $this->onSubmitCallbacks[] = $callback;
    }

    /**
     * Defines an object save callback.
     *
     * This will be executed when the form is submitted
     * after the object has been saved to the underlying data source.
     *
     * This will NOT be called on a details form.
     *
     * Example:
     * <code>
     * $form->onSave(function (Person $object, array $input) {
     *      $this->sendEmailToAdmin($object);
     * });
     * </code>
     *
     * @param callable $callback
     *
     * @return void
     */
    public function onSave(callable $callback)
    {
        $this->onSaveCallbacks[] = $callback;
    }

    /**
     * @return FinalizedCrudFormDefinition
     * @throws InvalidArgumentException
     */
    public function finalize()
    {
        if ($this->isCreateForm() && !$this->createObjectCallback) {
            throw InvalidArgumentException::format(
                    'Cannot finalize crud form definition for class %s in mode \'%s\': object constructor has not been defined, use ->%s()',
                    $this->class->getClassName(), $this->mode, 'createObjectType'
            );
        }

        $this->finishCurrentStage();

        $stages     = $this->stages;
        $firstStage = array_shift($stages);

        $stagedForm = new StagedForm($firstStage, $stages);

        return new FinalizedCrudFormDefinition(
                $this->mode,
                $stagedForm,
                $this->createObjectCallback,
                $this->onSubmitCallbacks,
                $this->onSaveCallbacks
        );
    }
}