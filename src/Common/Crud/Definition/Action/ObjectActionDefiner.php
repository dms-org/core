<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Action;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\Action\Object\CustomObjectActionHandler;
use Dms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\IObjectActionHandler;
use Dms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\Mapping\ObjectFormObjectMapping;
use Dms\Core\Common\Crud\Action\Object\Mapping\WrapperObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\ObjectAction;
use Dms\Core\Common\Crud\Form\ObjectForm;
use Dms\Core\Common\Crud\Form\ObjectStagedFormObject;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Builder\Form as FormBuilder;
use Dms\Core\Form\Builder\StagedForm as StagedFormBuilder;
use Dms\Core\Form\Builder\StagedForm;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Form\Object\FormObject;
use Dms\Core\Form\Object\Stage\StagedFormObject;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Module\Definition\ActionDefiner;
use Dms\Core\Module\IStagedFormDtoMapping;

/**
 * The object action definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectActionDefiner extends ActionDefiner
{
    /**
     * @var IIdentifiableObjectSet
     */
    protected $dataSource;

    /**
     * @var callable[]
     */
    protected $objectValidationCallbacks = [];

    /**
     * @var string|null
     */
    protected $currentObjectType;

    /**
     * @var string|null
     */
    protected $currentDataDtoType;

    /**
     * @inheritDoc
     */
    public function __construct(IIdentifiableObjectSet $dataSource, IAuthSystem $authSystem, array $requiredPermissions, $name, callable $callback)
    {
        parent::__construct($authSystem, $requiredPermissions, $name, $callback);

        $this->dataSource = $dataSource;
    }

    /**
     * Defines a callback to validate whether an object is supported in this object action.
     *
     * This MUST be called before the ->form(...) method.
     *
     * Example:
     * <code>
     * ->where(function (Person $person) {
     *      return $person->getAge() >= 60;
     * });
     * </code>
     *
     * @param callable $objectValidationCallback
     *
     * @return static
     */
    public function where(callable $objectValidationCallback)
    {
        $this->objectValidationCallbacks[] = $objectValidationCallback;
        return $this;
    }

    /**
     * Gets the first stage of the form wherein the object
     * from the data source is loaded.
     *
     * @return IForm
     */
    protected function getObjectFormStage() : IForm
    {
        $objectValidationCallbacks = $this->objectValidationCallbacks;

        if ($objectValidationCallbacks) {
            /** @var callable $validationCallback */
            $validationCallback = array_shift($objectValidationCallbacks);

            foreach ($objectValidationCallbacks as $otherCallback) {
                $validationCallback = function ($object) use ($validationCallback, $otherCallback) {
                    return $validationCallback($object) && $otherCallback($object);
                };
            }
        } else {
            $validationCallback = null;
        }

        return ObjectForm::build($this->dataSource, $validationCallback);
    }


    /**
     * Defines the following form stages after the loading the entity
     * in the first stage.
     *
     * You can pass a normal form as such:
     *
     * Example:
     * <code>
     * ->form(Form::create()->section('Section', [
     *      // Fields...
     * ])
     * </code>
     *
     * The supplied form can be dependent on the the chosen object
     * by passing a callback which will then define the staged form
     * with the object.
     *
     * Example:
     * <code>
     * ->form(function (StagedForm $form) {
     *      return $form->then(function (array $input) {
     *          if ($input['object'] instance Person) {
     *              return Form::create()->section('Person', [...]);
     *          } else {
     *              return Form::create()->section('Animal', [...]);
     *          }
     *      });
     * })
     * </code>
     *
     * You can also pass an instance of {@see ObjectStagedFormObject} that
     * can be dependent on the object.
     *
     * Example:
     * <code>
     * ->form(new CustomStagedFormObject($this->repository))
     * </code>
     *
     * @param IForm|IStagedForm|FormObject|StagedFormObject|FormBuilder|StagedFormBuilder|ObjectStagedFormObject|callable $form
     * @param IStagedFormDtoMapping|callable|null                                                                         $submissionToDtoMapping
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function form($form, $submissionToDtoMapping = null)
    {
        if (is_callable($form)) {
            $stagedForm = StagedForm::begin($this->getObjectFormStage());
            $form($stagedForm);
            $stagedForm = $stagedForm->build();

            $this->formDtoMappingCallback = function () use ($stagedForm) {
                return new ArrayObjectActionFormMapping($stagedForm);
            };
        } elseif ($form instanceof ObjectStagedFormObject) {
            $this->formDtoMappingCallback = function () use ($form) {
                return new ObjectFormObjectMapping($form);
            };
        } else {
            parent::form($form, $submissionToDtoMapping);
            $innerCallback = $this->formDtoMappingCallback;

            $this->formDtoMappingCallback = function ($handlerParameterType) use ($innerCallback) {
                /** @var IStagedFormDtoMapping $innerMapping */
                $innerMapping = $innerCallback($handlerParameterType);

                return new WrapperObjectActionFormMapping($this->getObjectFormStage(), $innerMapping);
            };
        }

        return $this;
    }

    /**
     * Defines the action handler. This will be executed when the action is run.
     *
     * Example with form:
     * <code>
     * ->handler(function (Person $object, ArrayDataObject $input) {
     *      $object->doSomething($input['data']);
     *      $this->repository->save($object);
     * });
     * </code>
     *
     * Example without form:
     * <code>
     * ->handler(function (Person $object) {
     *      $object->doSomething();
     *      $this->repository->save($object);
     * });
     * </code>
     *
     * @param callable|IObjectActionHandler $handler
     *
     * @return void
     */
    public function handler($handler)
    {
        if (!($handler instanceof IObjectActionHandler)) {
            $handler = new CustomObjectActionHandler($handler, $this->returnDtoType, $this->currentObjectType, $this->currentDataDtoType);
        }

        if (!$this->formDtoMappingCallback) {
            $formMapping = new WrapperObjectActionFormMapping($this->getObjectFormStage());
        } else {
            $formMapping = call_user_func($this->formDtoMappingCallback, $handler->getParameterTypeClass());
        }
        /** @var IObjectActionFormMapping $formMapping */

        call_user_func($this->callback, new ObjectAction(
                $this->name,
                $this->authSystem,
                $this->requiredPermissions,
                $formMapping,
                $handler
        ));
    }
}