<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Builder\Form as FormBuilder;
use Dms\Core\Form\Builder\StagedForm as StagedFormBuilder;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Form\Object\FormObject;
use Dms\Core\Form\Object\Stage\StagedFormObject;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Core\Module\Action\ParameterizedAction;
use Dms\Core\Module\Action\UnparameterizedAction;
use Dms\Core\Module\Handler\CustomParameterizedActionHandler;
use Dms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Dms\Core\Module\IParameterizedActionHandler;
use Dms\Core\Module\IStagedFormDtoMapping;
use Dms\Core\Module\IUnparameterizedActionHandler;
use Dms\Core\Module\Mapping\ArrayDataObjectFormMapping;
use Dms\Core\Module\Mapping\CustomStagedFormDtoMapping;
use Dms\Core\Module\Mapping\FormObjectMapping;
use Dms\Core\Module\Mapping\StagedFormObjectMapping;
use Dms\Core\Util\Debug;
use Dms\Core\Util\Reflection;

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
    protected $authSystem;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var callable|null
     */
    protected $formDtoMappingCallback = null;

    /**
     * @var IPermission[]
     */
    protected $requiredPermissions = [];

    /**
     * @var string|null
     */
    protected $returnDtoType = null;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * ActionDefiner constructor.
     *
     * @param IAuthSystem $authSystem
     * @param array       $requiredPermissions
     * @param string      $name
     * @param callable    $callback
     */
    public function __construct(IAuthSystem $authSystem, array $requiredPermissions, string $name, callable $callback)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'requiredPermissions', $requiredPermissions, IPermission::class);

        $this->name                = $name;
        $this->callback            = $callback;
        $this->authSystem          = $authSystem;
        $this->requiredPermissions = $requiredPermissions;
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
     * Sets the action's metadata
     *
     * @param array $metadata
     *
     * @return static
     */
    public function metadata(array $metadata)
    {
        $this->metadata += $metadata;

        return $this;
    }

    /**
     * Sets the return dto class type for the action.
     *
     * @param string $returnDtoType
     *
     * @return static
     */
    public function returns(string $returnDtoType)
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
                list($handler, $parameterDtoType) = $this->wrapArrayParameterAsDto($handler);
                $handler = new CustomParameterizedActionHandler($handler, $this->returnDtoType, $parameterDtoType);
            }

            /** @var IStagedFormDtoMapping $mapping */
            $mapping = call_user_func($this->formDtoMappingCallback, $handler->getParameterTypeClass());

            call_user_func($this->callback, new ParameterizedAction(
                $this->name,
                $this->authSystem,
                $this->requiredPermissions,
                $mapping,
                $handler,
                $this->metadata
            ));
        } else {

            if (!($handler instanceof IUnparameterizedActionHandler)) {
                $handler = new CustomUnparameterizedActionHandler($handler, $this->returnDtoType);
            }

            call_user_func($this->callback, new UnparameterizedAction(
                $this->name,
                $this->authSystem,
                $this->requiredPermissions,
                $handler,
                $this->metadata
            ));
        }
    }

    private function wrapArrayParameterAsDto(callable $handler) : array
    {
        $reflection = Reflection::fromCallable($handler);

        $parameter = $reflection->getParameters()[0] ?? null;

        if (!$parameter || !@$parameter->isArray()) {
            return [$handler, null];
        }

        $handler = function (ArrayDataObject $array) use ($handler) {
            return $handler($array->getArray());
        };

        return [$handler, ArrayDataObject::class];
    }
}