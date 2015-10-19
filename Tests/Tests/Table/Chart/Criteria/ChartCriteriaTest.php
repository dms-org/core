<?php

namespace Iddigital\Cms\Core\Tests\Table\Chart\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Criteria\OrderingDirection;
use Iddigital\Cms\Core\Table\Chart\Criteria\AxisCondition;
use Iddigital\Cms\Core\Table\Chart\Criteria\AxisOrdering;
use Iddigital\Cms\Core\Table\Chart\Criteria\ChartCriteria;
use Iddigital\Cms\Core\Table\Chart\Structure\ChartAxis;
use Iddigital\Cms\Core\Table\Chart\Structure\LineChart;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartCriteriaTest extends CmsTestCase
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

        $criteria = new ChartCriteria($structure);

        $this->assertSame($structure, $criteria->getStructure());
        $this->assertSame([], $criteria->getConditions());
        $this->assertSame([], $criteria->getOrderings());
    }

    public function testWhere()
    {
        $structure = $this->makeStructure();

        $criteria = new ChartCriteria($structure);

        $criteria->where('x', '>=', 0);

        $this->assertEquals([
                new AxisCondition($structure->getAxis('x'), $structure->getAxis('x')->getType()->getOperator('>='), 0)
        ], $criteria->getConditions());
    }

    public function testOrderBy()
    {
        $structure = $this->makeStructure();

        $criteria = new ChartCriteria($structure);

        $criteria->orderBy('x', OrderingDirection::DESC);

        $this->assertEquals([
                new AxisOrdering($structure->getAxis('x'), OrderingDirection::DESC)
        ], $criteria->getOrderings());
    }

    public function testOrderByAsc()
    {
        $structure = $this->makeStructure();
        $criteria = new ChartCriteria($structure);

        $criteria->orderByAsc('x')
                ->orderByDesc('x');

        $this->assertEquals([
                new AxisOrdering($structure->getAxis('x'), OrderingDirection::ASC),
                new AxisOrdering($structure->getAxis('x'), OrderingDirection::DESC),
        ], $criteria->getOrderings());
    }

    public function testFromExisting()
    {
        $structure = $this->makeStructure();
        $criteria = new ChartCriteria($structure);

        $criteria
                ->where('x', '>', 5)
                ->orderByAsc('x');

        $this->assertEquals(ChartCriteria::fromExisting($criteria), $criteria);
    }
}