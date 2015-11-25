<?php

namespace Iddigital\Cms\Core\Tests\Module\Fixtures;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Module\IChartDisplay;
use Iddigital\Cms\Core\Module\ITableDisplay;
use Iddigital\Cms\Core\Module\Module;

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


        $module->custom()->action($this->mockAction);
        $module->custom()->table($this->mockTable);
        $module->custom()->chart($this->mockChart);
    }
}