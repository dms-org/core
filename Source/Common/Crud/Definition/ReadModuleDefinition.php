<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Definition\Action\ObjectActionDefiner;
use Iddigital\Cms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Iddigital\Cms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Iddigital\Cms\Core\Common\Crud\Form\FormWithBinding;
use Iddigital\Cms\Core\Common\Crud\IReadModule;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Module\ITableDisplay;

/**
 * The read module definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModuleDefinition extends ModuleDefinition
{
    /**
     * @var string
     */
    protected $classType;

    /**
     * @var FinalizedClassDefinition
     */
    protected $class;

    /**
     * @var IEntitySet
     */
    protected $dataSource;

    /**
     * @var callable
     */
    protected $labelObjectCallback;

    /**
     * @var callable
     */
    protected $detailsFormDefinition;

    /**
     * @var ITableDisplay
     */
    protected $summaryTable;

    /**
     * ReadModuleDefinition constructor.
     *
     * @param IAuthSystem $authSystem
     * @param IEntitySet  $dataSource
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IAuthSystem $authSystem, IEntitySet $dataSource)
    {
        parent::__construct($authSystem);
        $this->dataSource = $dataSource;
        $this->classType  = $this->dataSource->getObjectType();


        if (!is_a($this->classType, TypedObject::class, true)) {
            throw InvalidArgumentException::format(
                    'Class type from data source must be an instance of %s, %s given',
                    TypedObject::class, $this->classType
            );
        }

        /** @var string|TypedObject $classType */
        $classType   = $this->classType;
        $this->class = $classType::definition();
    }

    /**
     * Defines an action that operates on an object from the data source.
     *
     * This creates a parameterized action that with a staged form
     * with the fist form stage loading the entity
     *
     * @param string $name
     *
     * @return ObjectActionDefiner
     */
    public function objectAction($name)
    {
        return new ObjectActionDefiner(
                $this->dataSource,
                $this->authSystem,
                $name,
                function (IObjectAction $action) {
                    $this->actions[$action->getName()] = $action;
                }
        );
    }

    /**
     * Defines the method of how to create a human readable
     * label for an object.
     *
     * @return LabelObjectStrategyDefiner
     */
    public function labelObjects()
    {
        return new LabelObjectStrategyDefiner($this->classType, function (callable $labelObjectCallback) {
            $this->labelObjectCallback = $labelObjectCallback;
        });
    }

    /**
     * Defines the object details form.
     *
     * The callback is passed an instance of {@see CrudFormDefinition}.
     *
     * Example:
     * <code>
     * $module->crudForm(function (CrudFormDefinition $form) {
     *      $form->section('Details', [
     *              $form->field(Field::name('name')->label('Label')->string()->required())
     *                      ->bindToProperty('name'),
     *              // ...
     *      ]);
     * });
     * </code>
     *
     * @param callable $formDefinitionCallback
     *
     * @return void
     */
    public function crudForm(callable $formDefinitionCallback)
    {
        $definition = $this->loadCrudFormDefinition(CrudFormDefinition::MODE_DETAILS, $formDefinitionCallback);

        $this->objectAction(IReadModule::DETAILS_ACTION)
                ->authorize('')// TODO: Permission
                ->returns(IForm::class)
                ->handler(function (IEntity $entity) use ($definition) {
                    $stages = $definition->getStagedForm()->withSubmittedFirstStage([
                            IObjectAction::OBJECT_FIELD_NAME => $entity
                    ]);

                    $form         = Form::create();
                    $previousData = [];

                    foreach ($stages->getAllStages() as $stage) {
                        /** @var FormWithBinding $currentStageForm */
                        $currentStageForm = $stage->loadForm($previousData);
                        $currentStageForm = $currentStageForm->getBinding()->getForm($entity);

                        $form->embed($currentStageForm);
                        $previousData += $currentStageForm->getInitialValues();
                    }

                    return $form->build();
                });
    }

    protected function loadCrudFormDefinition($mode, callable $callback)
    {
        $definition = new CrudFormDefinition($this->dataSource, $this->class, $mode);
        $callback($definition);

        return $definition->finalize();
    }

    /**
     * Defines the structure of the summary table for the module.
     *
     * Example:
     * <code>
     * ->summaryTable(function (SummaryTableDefinition $table) {
     *      $table->column(Column::name('name')->label('Name')->components([
     *              Field::name('first_name')->label('First Name')->string(),
     *              Field::name('last_name')->label('Last Name')->string(),
     *      ]));
     *
     *      $table->mapProperty('firstName')->toComponent('name.first_name');
     *      $table->mapProperty('lastName')->toComponent('name.last_name');
     *      $table->mapProperty('age')->to(Field::name('age')->label('Age')->int());
     *
     *      $table->view('default', 'Default')
     *              ->asDefault()
     *              ->loadAll()
     *              ->orderByAsc(['product_name']);
     *
     *      $table->view('category', 'Category')
     *              ->loadAll()
     *              ->groupBy('category.id')
     *              ->orderByAsc(['category.name', 'category_sort_order'])
     *              ->withReorder(function (Person $entity, $newOrderIndex) {
     *                  $this->repository->reorderPersonInCategory($entity, $newOrderIndex);
     *              });
     * });
     * </code>
     *
     * @param callable $summaryTableDefinitionCallback
     *
     * @return void
     */
    public function summaryTable(callable $summaryTableDefinitionCallback)
    {
        $definition = new SummaryTableDefinition($this, $this->class, $this->dataSource);
        $summaryTableDefinitionCallback($definition);

        $this->summaryTable = $definition->finalize();
    }
}