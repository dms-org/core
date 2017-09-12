<?php

namespace Dms\Core\Tests\Table\Chart\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Chart\Criteria\AxisCondition;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Chart\Structure\LineChart;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AxisConditionTest extends CmsTestCase
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

        $condition = new AxisCondition(
                $axis = $structure->getAxis('x'),
                $operator = $structure->getAxis('x')->getType()->getOperator('>'),
                5
        );

        $this->assertSame($axis, $condition->getAxis());
        $this->assertSame($operator, $condition->getOperator());
        $this->assertSame(5, $condition->getValue());
    }

    public function testInvalidType()
    {
        $this->expectException(TypeMismatchException::class);

        $structure = $this->makeStructure();

        new AxisCondition(
                $structure->getAxis('x'),
                $structure->getAxis('x')->getType()->getOperator('>'),
                'some-string'
        );
    }
}