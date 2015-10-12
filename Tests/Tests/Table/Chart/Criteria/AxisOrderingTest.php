<?php

namespace Iddigital\Cms\Core\Tests\Table\Chart\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Criteria\OrderingDirection;
use Iddigital\Cms\Core\Table\Chart\Criteria\AxisOrdering;
use Iddigital\Cms\Core\Table\Chart\Structure\ChartAxis;
use Iddigital\Cms\Core\Table\Chart\Structure\LineChart;

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
        $this->setExpectedException(InvalidArgumentException::class);

        $structure = $this->makeStructure();

        new AxisOrdering(
                $structure->getAxis('x'),
                'invalid-direction'
        );
    }
}