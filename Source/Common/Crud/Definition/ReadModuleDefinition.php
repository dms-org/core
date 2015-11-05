<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Definition\Action\ObjectActionDefiner;
use Iddigital\Cms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
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
    protected $crudFormDefinitionCallback;

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
     * The callback is passed an instance of {@see CrudFormDefinition} and the
     * object instance of which the details are referring.
     *
     * Example:
     * <code>
     * $module->crudForm(function (CrudFormDefinition $form, Person $person = null) {
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
        $this->crudFormDefinitionCallback = $formDefinitionCallback;
    }

    /**
     * Defines the structure of the summary table for the module.
     *
     * Example:
     * <code>
     * ->summaryTable(function (SummaryTableDefinition $map) {
     *      // TODO: example
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