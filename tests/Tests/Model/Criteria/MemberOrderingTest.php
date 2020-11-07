<?php

namespace Dms\Core\Tests\Model\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Dms\Core\Model\Criteria\MemberOrdering;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberOrderingTest extends CmsTestCase
{
    protected function member()
    {
        return new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)]);
    }

    public function testNewPropertyOrdering()
    {
        $ordering = new MemberOrdering($this->member(), OrderingDirection::ASC);

        $this->assertEquals($this->member(), $ordering->getNestedMember());
        $this->assertSame(OrderingDirection::ASC, $ordering->getDirection());
    }

    public function testOrderCallable()
    {
        $ordering = new MemberOrdering($this->member(), OrderingDirection::ASC);

        $callable = $ordering->getArrayOrderCallable();
        $this->assertIsCallable($callable);
        $this->assertSame([1 => 'abc'], $callable([1 => new TestEntity(null, 'abc')]));
        $this->assertSame([3 => 'FOO'], $callable([3 => new TestEntity(null, 'FOO')]));
    }
}