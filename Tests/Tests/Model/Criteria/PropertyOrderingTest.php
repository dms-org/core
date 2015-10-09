<?php

namespace Iddigital\Cms\Core\Tests\Model\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Criteria\NestedProperty;
use Iddigital\Cms\Core\Model\Criteria\OrderingDirection;
use Iddigital\Cms\Core\Model\Criteria\PropertyOrdering;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyOrderingTest extends CmsTestCase
{
    protected function property()
    {
        return new NestedProperty([TestEntity::definition()->getProperty('prop')]);
    }

    public function testNewPropertyOrdering()
    {
        $ordering = new PropertyOrdering($this->property(), OrderingDirection::ASC);

        $this->assertSame($this->property()->getNestedProperties(), $ordering->getNestedProperties());
        $this->assertSame(OrderingDirection::ASC, $ordering->getDirection());
    }

    public function testOrderCallable()
    {
        $ordering = new PropertyOrdering($this->property(), OrderingDirection::ASC);

        $callable = $ordering->getOrderCallable();
        $this->assertInternalType('callable', $callable);
        $this->assertSame('abc', $callable(new TestEntity(null, 'abc')));
        $this->assertSame('FOO', $callable(new TestEntity(null, 'FOO')));
    }
}