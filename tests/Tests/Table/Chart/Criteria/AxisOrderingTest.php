<?php

namespace Dms\Core\Tests\Table\Chart\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\Chart\Criteria\AxisOrdering;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Chart\Structure\LineChart;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AxisOrderingTest extends CmsTestCase
{
    protected function makeStructure()
    {
        return new LineChart(
                ChartAxis::forField(Field::name('x')->label('X-Axis')->int()->build()),
                ChartAxis::forField(Field::name('y')->label('Y-Axis')->int()->build())
        );
    }

    public function testNew()
    {
        $structure = $this->makeStructure();

        $ordering = new AxisOrdering(
                $axis = $structure->getAxis('x'),
                OrderingDirection::ASC
        );

        $this->assertSame($axis, $ordering->getAxis());
        $this->assertSame(OrderingDirection::ASC, $ordering->getDirection());
        $this->assertSame(true, $ordering->isAsc());
    }

    public function testInvalidOrderingDirection()
    {
        $this->expectException(InvalidArgumentException::class);

        $structure = $this->makeStructure();

        new AxisOrdering(
                $structure->getAxis('x'),
                'invalid-direction'
        );
    }
}