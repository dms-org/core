<?php

namespace Dms\Core\Tests\Table\Chart\Structure;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Chart\Structure\BarChart;
use Dms\Core\Table\Column\Component\ColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BarChartTest extends CmsTestCase
{
    public function testNew()
    {
        $chart = new BarChart(
                $x = ChartAxis::forField(Field::name('x')->label('X-Axis')->int()->build()),
                $y = ChartAxis::forField(Field::name('y')->label('Y-Axis')->int()->build())
        );

        $this->assertSame(['x' => $x, 'y' => $y], $chart->getAxes());
        $this->assertSame(true, $chart->hasAxis('x'));
        $this->assertSame(true, $chart->hasAxis('y'));;
        $this->assertSame(false, $chart->hasAxis('z'));
        $this->assertSame($x, $chart->getAxis('x'));
        $this->assertSame($y, $chart->getAxis('y'));
        $this->assertSame($x, $chart->getHorizontalAxis());
        $this->assertSame($y, $chart->getVerticalAxis());

        $this->assertThrows(function () use ($chart) {
            $chart->getAxis('z');
        }, InvalidArgumentException::class);
    }

    public function testHorizontalAxisMustHaveOnlyOneComponent()
    {
        $this->expectException(InvalidArgumentException::class);

        new BarChart(
            new ChartAxis('x', 'X-Axis', [
                    ColumnComponent::forField(Field::name('x1')->label('X1')->int()->build()),
                    ColumnComponent::forField(Field::name('x2')->label('X2')->int()->build()),
            ]),
            ChartAxis::forField(Field::name('y')->label('Y-Axis')->int()->build())
        );
    }
}