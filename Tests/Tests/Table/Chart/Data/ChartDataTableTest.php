<?php

namespace Iddigital\Cms\Core\Tests\Table\Chart\Data;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Table\Chart\Data\ChartDataTable;
use Iddigital\Cms\Core\Table\Chart\IChartStructure;

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