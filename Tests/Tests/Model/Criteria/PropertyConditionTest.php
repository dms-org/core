<?php

namespace Iddigital\Cms\Core\Tests\Model\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Model\Criteria\Condition\PropertyCondition;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyConditionTest extends CmsTestCase
{
    protected function property()
    {
        return TestEntity::definition()->getProperty('prop');
    }

    public function testNewPropertyCondition()
    {
        $condition = new PropertyCondition([$this->property()], '=', 'foo');

        $this->assertSame([$this->property()], $condition->getNestedProperties());
        $this->assertSame('=', $condition->getOperator());
        $this->assertSame('foo', $condition->getValue());
    }

    public function testEqualsFilterCallable()
    {
        $condition = new PropertyCondition([$this->property()], '=', 'foo');

        $callable = $condition->getFilterCallable();
        $this->assertInternalType('callable', $callable);
        $this->assertFalse($callable(new TestEntity(null, 'abc')));
        $this->assertFalse($callable(new TestEntity(null, 'FOO')));
        $this->assertTrue($callable(new TestEntity(null, 'foo')));
    }

    public function testNotEqualsFilterCallable()
    {
        $condition = new PropertyCondition([$this->property()], '!=', 'foo');

        $callable = $condition->getFilterCallable();
        $this->assertTrue($callable(new TestEntity(null, 'abc')));
        $this->assertTrue($callable(new TestEntity(null, 'FOO')));
        $this->assertFalse($callable(new TestEntity(null, 'foo')));
    }

    public function testInFilterCallable()
    {
        $condition = new PropertyCondition([$this->property()], ConditionOperator::IN, ['foo', 'bar']);

        $callable = $condition->getFilterCallable();
        $this->assertTrue($callable(new TestEntity(null, 'foo')));
        $this->assertTrue($callable(new TestEntity(null, 'bar')));
        $this->assertFalse($callable(new TestEntity(null, 'baz')));
        $this->assertFalse($callable(new TestEntity(null, '')));

        $this->assertThrows(function () {
            new PropertyCondition([$this->property()], ConditionOperator::IN, [123]);
        }, TypeMismatchException::class);
    }

    public function testNotInFilterCallable()
    {
        $condition = new PropertyCondition([$this->property()], ConditionOperator::NOT_IN, ['foo', 'bar']);

        $callable = $condition->getFilterCallable();
        $this->assertTrue($callable(new TestEntity(null, 'baz')));
        $this->assertTrue($callable(new TestEntity(null, '')));
        $this->assertFalse($callable(new TestEntity(null, 'foo')));
        $this->assertFalse($callable(new TestEntity(null, 'bar')));

        $this->assertThrows(function () {
            new PropertyCondition([$this->property()], ConditionOperator::NOT_IN, ['foo', null]);
        }, TypeMismatchException::class);
    }

    public function testStringContainsFilterCallable()
    {
        $condition = new PropertyCondition([$this->property()], ConditionOperator::STRING_CONTAINS, 'foo');

        $callable = $condition->getFilterCallable();
        $this->assertTrue($callable(new TestEntity(null, 'fooo')));
        $this->assertTrue($callable(new TestEntity(null, 'abcfooabc')));
        $this->assertFalse($callable(new TestEntity(null, 'abcfOoabc')));
    }

    public function testStringContainsCaseInsensitiveFilterCallable()
    {
        $condition = new PropertyCondition([$this->property()], ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE, 'foo');

        $callable = $condition->getFilterCallable();
        $this->assertTrue($callable(new TestEntity(null, 'fooo')));
        $this->assertTrue($callable(new TestEntity(null, 'abcfooabc')));
        $this->assertTrue($callable(new TestEntity(null, 'abcfOoabc')));
        $this->assertFalse($callable(new TestEntity(null, 'bar')));
    }
}