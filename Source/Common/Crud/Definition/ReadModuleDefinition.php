<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Action\Crud\ViewDetailsAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Definition\Action\ObjectActionDefiner;
use Iddigital\Cms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Iddigital\Cms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Iddigital\Cms\Core\Common\Crud\IReadModule;
use Iddigital\Cms\Core\Common\Crud\Table\ISummaryTable;
use Iddigital\Cms\Core\Common\Crud\UnsupportedActionException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;

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
     * @var ISummaryTable
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
     * If the form mode is not supported {@see CrudFormDefinition::isEditForm},
     * {@see CrudFormDefinition::isCreateForm}, {@see CrudFormDefinition::isDetailsForm},
     * throw an exception of type {@see UnsupportedActionException}.
     *
     * Example:
     * <code>
     * $module->crudForm(function (CrudFormDefinition $form) {
     *      if ($form->isEditForm()) {
     *          throw new UnsupportedActionException();
     *      }
     *      // ...
     * });
     * </code>
     *
     * @param callable $formDefinitionCallback
     *
     * @return void
     */
    public function crudForm(callable $formDefinitionCallback)
    {
        $this->loadDetailsAction($formDefinitionCallback);
    }

    protected function loadDetailsAction(callable $formDefinitionCallback)
    {
        $definition = $this->loadCrudFormDefinition(CrudFormDefinition::MODE_DETAILS, $formDefinitionCallback);

        if (!$definition) {
            return;
        }

        $this->custom()->action(new ViewDetailsAction($this->dataSource, $this->authSystem, $definition));
    }

    protected function loadCrudFormDefinition($mode, callable $callback)
    {
        $definition = new CrudFormDefinition($this->dataSource, $this->class, $mode);

        try {
            $callback($definition);
        } catch (UnsupportedActionException $e) {
            return null;
        }

        return $definition->finalize();
    }

    /**
     * Defines the structure of the summary table for the module.
     *
     * Example:
     * <code>
     * $module->summaryTable(function (SummaryTableDefinition $table) {
     *      $table->column(Column::name('name')->label('Name')->components([
     *              Field::name('first_name')->label('First Name')->string(),
     *              Field::name('last_name')->label('Last Name')->string(),
     *      ]));
     *
     *      $table->mapProperty('firstName')->toComponent('name.first_name');
     *      $table->mapProperty('lastName')->toComponent('name.last_name');
     *      $table->mapProperty('age')->to(Field::name('age')->label('Age')->int());
     *      $table->mapProperty('ageSortIndex')->to(Field::name('age_sort_index')->label('Age')->int());
     *
     *      $table->view('default', 'Default')
     *              ->asDefault()
     *              ->loadAll()
     *              ->orderByAsc(['name.first_name', 'name.last_name']);
     *
     *      $table->view('category', 'Category')
     *              ->loadAll()
     *              ->groupBy('age')
     *              ->orderByAsc(['age', 'age_sort_index'])
     *              ->withReorder(function (Person $entity, $newOrderIndex) {
     *                  $this->repository->reorderPersonInAgeGroup($entity, $newOrderIndex);
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

        $this->action(IReadModule::SUMMARY_TABLE_ACTION)
                ->authorize(IReadModule::VIEW_PERMISSION)
                ->returns(ISummaryTable::class)
                ->handler(function () {
                    return $this->summaryTable;
                });
    }

    /**
     * @return FinalizedReadModuleDefinition
     * @throws InvalidOperationException
     */
    public function finalize()
    {
        $this->verifyCanBeFinalized();

        return new FinalizedReadModuleDefinition(
                $this->name,
                $this->labelObjectCallback,
                $this->summaryTable,
                $this->actions,
                $this->tables,
                $this->charts,
                $this->widgets
        );
    }

    /**
     * @inheritDoc
     */
    protected function verifyCanBeFinalized()
    {
        parent::verifyCanBeFinalized();

        if (!$this->labelObjectCallback) {
            throw InvalidOperationException::format(
                    'Cannot finalize definition for module \'%s\': label objects callback has not been defined',
                    $this->name
            );
        }

        if (!$this->summaryTable) {
            throw InvalidOperationException::format(
                    'Cannot finalize definition for module \'%s\': summary table has not been defined',
                    $this->name
            );
        }
    }
}