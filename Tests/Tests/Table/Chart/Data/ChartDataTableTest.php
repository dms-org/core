<?php

namespace Dms\Core\Tests\Table\Chart\Data;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Table\Chart\Data\ChartDataTable;
use Dms\Core\Table\Chart\IChartStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartDataTableTest extends CmsTestCase
{
    public function testNew()
    {
        /** @var IChartStructure|\PHPUnit_Framework_MockObject_MockObject $structure */
        $structure = $this->getMockForAbstractClass(IChartStructure::class);
        $data      = new ChartDataTable($structure, [
                ['x' => ['x' => 1]],
                ['x' => ['x' => 1]],
                ['x' => ['x' => 2]],
        ]);

        $this->assertSame($structure, $data->getStructure());
        $this->assertSame([
                ['x' => ['x' => 1]],
                ['x' => ['x' => 1]],
                ['x' => ['x' => 2]],
        ], $data->getRows());
    }
}