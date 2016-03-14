<?php

namespace Dms\Core\Tests\Module\Fixtures;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Module\Definition\ModuleDefinition;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Module\Module;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithCustom extends Module
{
    /**
     * @var IAction
     */
    private $mockAction;

    /**
     * @var ITableDisplay
     */
    private $mockTable;

    /**
     * @var IChartDisplay
     */
    private $mockChart;

    /**
     * @inheritDoc
     */
    public function __construct(IAuthSystem $authSystem, IAction $mockAction, ITableDisplay $mockTable, IChartDisplay $mockChart)
    {
        $this->mockAction = $mockAction;
        $this->mockTable  = $mockTable;
        $this->mockChart  = $mockChart;

        parent::__construct($authSystem);
    }


    /**
     * Defines the module.
     *
     * @param ModuleDefinition $module
     *
     * @return void
     */
    protected function define(ModuleDefinition $module)
    {
        $module->name('test-module-with-custom');

        $module->metadata([
            'key' => 'some-metadata'
        ]);

        $module->custom()->action($this->mockAction);
        $module->custom()->table($this->mockTable);
        $module->custom()->chart($this->mockChart);
    }
}