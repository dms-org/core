<?php

namespace Iddigital\Cms\Core\Module\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Builder\Form as FormBuilder;
use Iddigital\Cms\Core\Form\Builder\StagedForm as StagedFormBuilder;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Form\Object\FormObject;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObject;
use Iddigital\Cms\Core\Module\Action\ParameterizedAction;
use Iddigital\Cms\Core\Module\Action\UnparameterizedAction;
use Iddigital\Cms\Core\Module\Handler\CustomParameterizedActionHandler;
use Iddigital\Cms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\IUnparameterizedActionHandler;
use Iddigital\Cms\Core\Module\Mapping\ArrayDataObjectFormMapping;
use Iddigital\Cms\Core\Module\Mapping\CustomStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\Mapping\FormObjectMapping;
use Iddigital\Cms\Core\Module\Mapping\StagedFormObjectMapping;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The action definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionDefiner
{
    /**
     * @var IAuthSystem
     */
    private $authSystem;

    /**
     * @var string
     */
    private $name;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var callable|null
     */
    private $formDtoMappingCallback = null;

    /**
     * @var IPermission[]
     */
    private $requiredPermissions = [];

    /**
     * @var string|null
     */
    private $returnDtoType = null;

    /**
     * ActionDefiner constructor.
     *
     * @param IAuthSystem $authSystem
     * @param string      $name
     * @param callable    $callback
     */
    public function __construct(IAuthSystem $authSystem, $name, callable $callback)
    {
        $this->name       = $name;
        $this->callback   = $callback;
        $this->authSystem = $authSystem;
    }

    /**
     * Sets the required form to be submitted for the action.
     *
     * If the supplied forms are an instance of {@see FormObject} or {@see StagedFormObject}
     * the form submission will be bound to an instance of the form object.
     *
     * Example:
     * <code>
     * ->form(new SomeFormObject())
     * ->handler(function (SomeFormObject $input) {
     *      // ...
     * })
     * </code>
     *
     * If however the supplied form is just a plain {@see IForm} or {@see IStagedForm},
     * you can supply your own custom form submission mapping as the second parameter.
     *
     * Example:
     * <code>
     * ->form(
     *      Form::create()->section('Section', [
     *          // Fields...
     *      ]),
     *      function (array $input) {
     *           return new SomeDto($input['data']);
     *      }
     * )->handler(function (SomeDto $input) {
     *      // ...
     * })
     * </code>
     *
     * If no mapping is supplied, the form submission will automatically mapped to an
     * instance of {@see ArrayDataObject} containing the supplied form data. This can
     * be used just as a normal array.
     *
     * Example:
     * <code>
     * ->form(Form::create()->section('Section', [
     *      // Fields...
     * ]))->handler(function (ArrayDataObject $input) {
     *      // ...
     * })
     * </code>
     *
     * @param IForm|IStagedForm|FormObject|StagedFormObject|FormBuilder|StagedFormBuilder $form
     * @param IStagedFormDtoMapping|callable|null                                         $submissionToDtoMapping
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function form($form, $submissionToDtoMapping = null)
    {
        $stagedForm = null;

        if ($form instanceof FormBuilder) {
            $form = $form->build();
        } elseif ($form instanceof StagedFormBuilder) {
            $form = $form->build();
        }

        if ($form instanceof IForm) {
            $stagedForm = $form->asStagedForm();
        } elseif ($form instanceof IStagedForm) {
            $stagedForm = $form;
        } else {
            throw InvalidArgumentException::format(
                    'Invalid form supplied to %s: expecting %s, %s given',
                    __METHOD__, implode('|', [IForm::class, IStagedForm::class, FormBuilder::class, StagedFormBuilder::class]),
                    Debug::getType($form)
            );
        }

        $this->formDtoMappingCallback = function ($handlerParameterType) use ($form, $stagedForm, $submissionToDtoMapping) {

            if ($form instanceof FormObject) {
                $mapping = new FormObjectMapping($form);
            } elseif ($form instanceof StagedFormObject) {
                $mapping = new StagedFormObjectMapping($form);
            } elseif ($submissionToDtoMapping instanceof IStagedFormDtoMapping) {
                $mapping = $submissionToDtoMapping;
            } elseif (is_callable($submissionToDtoMapping)) {
                $mapping = new CustomStagedFormDtoMapping($stagedForm, $handlerParameterType, $submissionToDtoMapping);
            } else {
                $mapping = new ArrayDataObjectFormMapping($stagedForm);
            }

            return $mapping;
        };


        return $this;
    }

    /**
     * Adds a required permission for executing this action.
     *
     * @param string|IPermission $permission
     *
     * @return static
     */
    public function authorize($permission)
    {
        $this->requiredPermissions[] =
                $permission instanceof IPermission
                        ? $permission
                        : Permission::named($permission);

        return $this;
    }

    /**
     * Adds an array of required permissions for executing this action.
     *
     * @param string[]|IPermission[] $permissions
     *
     * @return static
     */
    public function authorizeAll(array $permissions)
    {
        foreach ($permissions as $permission) {
            $this->authorize($permission);
        }

        return $this;
    }

    /**
     * Sets the return dto class type for the action.
     *
     * @param string $returnDtoType
     *
     * @return static
     */
    public function returns($returnDtoType)
    {
        $this->returnDtoType = $returnDtoType;

        return $this;
    }

    /**
     * Defines the action handler. This will be executed when the action is run.
     *
     * @param callable|IParameterizedActionHandler|IUnparameterizedActionHandler $handler
     *
     * @return void
     */
    public function handler($handler)
    {
        if ($this->formDtoMappingCallback) {
            if (!($handler instanceof IParameterizedActionHandler)) {
                $handler = new CustomParameterizedActionHandler($handler, $this->returnDtoType);
            }

            /** @var IStagedFormDtoMapping $mapping */
            $mapping = call_user_func($this->formDtoMappingCallback, $handler->getDtoType());

            call_user_func($this->callback, new ParameterizedAction(
                    $this->name,
                    $this->authSystem,
                    $this->requiredPermissions,
                    $mapping,
                    $handler
            ));
        } else {

            if (!($handler instanceof IUnparameterizedActionHandler)) {
                $handler = new CustomUnparameterizedActionHandler($handler, $this->returnDtoType);
            }

            call_user_func($this->callback, new UnparameterizedAction(
                    $this->name,
                    $this->authSystem,
                    $this->requiredPermissions,
                    $handler
            ));
        }
    }
}