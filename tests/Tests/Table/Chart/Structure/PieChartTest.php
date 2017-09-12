<?php

namespace Dms\Core\Tests\Table\Chart\Structure;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Chart\Structure\LineChart;
use Dms\Core\Table\Chart\Structure\PieChart;
use Dms\Core\Table\Column\Component\ColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PieChartTest extends CmsTestCase
{
    public function testNew()
    {
        $chart = new PieChart(
                $type = ChartAxis::forField(Field::name('type')->label('Type')->int()->build()),
                $amount = ChartAxis::forField(Field::name('amount')->label('Amount')->int()->build())
        );

        $this->assertSame(['type' => $type, 'amount' => $amount], $chart->getAxes());
        $this->assertSame(true, $chart->hasAxis('type'));
        $this->assertSame(true, $chart->hasAxis('amount'));;
        $this->assertSame(false, $chart->hasAxis('other'));
        $this->assertSame($type, $chart->getAxis('type'));
        $this->assertSame($amount, $chart->getAxis('amount'));
        $this->assertSame($type, $chart->getTypeAxis());
        $this->assertSame($amount, $chart->getValueAxis());

        $this->assertThrows(function () use ($chart) {
            $chart->getAxis('other');
        }, InvalidArgumentException::class);
    }

    public function testTypeAxisMustHaveOnlyOneComponent()
    {
        $this->expectException(InvalidArgumentException::class);

        new PieChart(
                new ChartAxis('type', 'Type', [
                        ColumnComponent::forField(Field::name('t1')->label('T1')->int()->build()),
                        ColumnComponent::forField(Field::name('t2')->label('T2')->int()->build()),
                ]),
                ChartAxis::forField(Field::name('amount')->label('Amount')->int()->build())
        );
    }

    public function testValueAxisMustHaveOnlyOneComponent()
    {
        $this->expectException(InvalidArgumentException::class);

        new PieChart(
                ChartAxis::forField(Field::name('type')->label('Type')->int()->build()),
                new ChartAxis('amount', 'Amount', [
                        ColumnComponent::forField(Field::name('a1')->label('A1')->int()->build()),
                        ColumnComponent::forField(Field::name('a2')->label('A2')->int()->build()),
                ])
        );
    }
}