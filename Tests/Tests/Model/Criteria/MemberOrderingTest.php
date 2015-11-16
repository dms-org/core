<?php

namespace Iddigital\Cms\Core\Tests\Model\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Iddigital\Cms\Core\Model\Criteria\MemberOrdering;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\Criteria\OrderingDirection;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

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

        $this->assertEquals($this->member()->getParts(), $ordering->getNestedMembers());
        $this->assertSame(OrderingDirection::ASC, $ordering->getDirection());
    }

    public function testOrderCallable()
    {
        $ordering = new MemberOrdering($this->member(), OrderingDirection::ASC);

        $callable = $ordering->getArrayOrderCallable();
        $this->assertInternalType('callable', $callable);
        $this->assertSame([1 => 'abc'], $callable([1 => new TestEntity(null, 'abc')]));
        $this->assertSame([3 => 'FOO'], $callable([3 => new TestEntity(null, 'FOO')]));
    }
}