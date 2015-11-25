<?php

namespace Iddigital\Cms\Core\Tests\Module;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Module\IChartDisplay;
use Iddigital\Cms\Core\Module\IParameterizedAction;
use Iddigital\Cms\Core\Module\ITableDisplay;
use Iddigital\Cms\Core\Module\IUnparameterizedAction;
use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Tests\Module\Fixtures\ModuleWithCustom;
use Iddigital\Cms\Core\Tests\Module\Fixtures\TestDto;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;

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
}