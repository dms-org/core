<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Definition\Action\ObjectActionDefiner;
use Iddigital\Cms\Core\Model\IObjectSet;
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
     * @var IObjectSet
     */
    protected $dataSource;

    /**
     * @var callable
     */
    protected $labelObjectCallback;

    /**
     * @var ITableDisplay
     */
    protected $summaryTable;

    /**
     * ReadModuleDefinition constructor.
     *
     * @param IAuthSystem $authSystem
     * @param IObjectSet  $dataSource
     */
    public function __construct(IAuthSystem $authSystem, IObjectSet $dataSource)
    {
        parent::__construct($authSystem);
        $this->dataSource = $dataSource;
        $this->classType  = $this->dataSource->getObjectType();
    }

    /**
     * Defines an action that operates on an object from the data source.
     *
     * @param string $name
     *
     * @return ObjectActionDefiner
     */
    public function objectAction($name)
    {
        return $this->action($name)
                ->
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
        $definition = new SummaryTableDefinition($this);
        $summaryTableDefinitionCallback($definition);

        $this->summaryTable = $definition->finalize();
    }
}