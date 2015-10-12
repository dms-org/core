<?php

namespace Iddigital\Cms\Core\Tests\Table\Chart\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Chart\Criteria\AxisCondition;
use Iddigital\Cms\Core\Table\Chart\Structure\ChartAxis;
use Iddigital\Cms\Core\Table\Chart\Structure\LineChart;

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
        $this->setExpectedException(TypeMismatchException::class);

        $structure = $this->makeStructure();

        new AxisCondition(
                $structure->getAxis('x'),
                $structure->getAxis('x')->getType()->getOperator('>'),
                'some-string'
        );
    }
}