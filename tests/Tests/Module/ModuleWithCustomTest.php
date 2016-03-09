<?php

namespace Dms\Core\Tests\Module;

use Dms\Core\Auth\IPermission;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Module\Module;
use Dms\Core\Tests\Module\Fixtures\ModuleWithCustom;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithCustomTest extends ModuleTestBase
{
    /**
     * @var IAction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAction;

    /**
     * @var ITableDisplay|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockTable;

    /**
     * @var IChartDisplay|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockChart;


    /**
     * @param MockAuthSystem $authSystem
     *
     * @return Module
     */
    protected function buildModule(MockAuthSystem $authSystem)
    {
        $this->mockAction = $this->getMockForAbstractClass(IParameterizedAction::class);
        $this->mockAction
                ->method('getName')
                ->willReturn('mock-action');
        $this->mockAction
                ->method('getRequiredPermissions')
                ->willReturn([]);

        $this->mockTable = $this->getMockForAbstractClass(ITableDisplay::class);
        $this->mockTable
                ->method('getName')
                ->willReturn('mock-table');

        $this->mockChart = $this->getMockForAbstractClass(IChartDisplay::class);
        $this->mockChart
                ->method('getName')
                ->willReturn('mock-chart');

        return new ModuleWithCustom($authSystem, $this->mockAction, $this->mockTable, $this->mockChart);
    }

    /**
     * @return IPermission[]
     */
    protected function expectedPermissions()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function expectedRequiredPermissions()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function expectedName()
    {
        return 'test-module-with-custom';
    }

    public function testActionGetters()
    {
        $this->assertCount(1, $this->module->getActions());
        $this->assertSame($this->mockAction, $this->module->getAction('mock-action'));
    }

    public function testTableGetters()
    {
        $this->assertCount(1, $this->module->getTables());
        $this->assertSame($this->mockTable, $this->module->getTable('mock-table'));
    }

    public function testChartGetters()
    {
        $this->assertCount(1, $this->module->getCharts());
        $this->assertSame($this->mockChart, $this->module->getChart('mock-chart'));
    }

    public function testPackageName()
    {
        // Not needed
    }
}