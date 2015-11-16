<?php

namespace Iddigital\Cms\Core\Tests\Model\Criteria\Member;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberPropertyExpressionTest extends CmsTestCase
{
    public function testNewNonNullable()
    {
        $member = new MemberPropertyExpression($prop = TestEntity::definition()->getProperty('prop'), false);

        $this->assertEquals(TestEntity::type(), $member->getSourceType());
        $this->assertEquals(Type::string(), $member->getResultingType());;
        $this->assertSame($prop, $member->getProperty());
        $this->assertSame('prop', $member->asString());

        $getter = $member->createArrayGetterCallable();
        $this->assertInternalType('callable', $getter);
        $this->assertSame(TestEntity::class, (new \ReflectionFunction($getter))->getClosureScopeClass()->getName());

        $this->assertSame([1 => 'abc'], $getter([1 => new TestEntity(null, 'abc')]));
    }

    public function testNewNullable()
    {
        $member = new MemberPropertyExpression($prop = TestEntity::definition()->getProperty('prop'), true);

        $this->assertEquals(TestEntity::type()->nullable(), $member->getSourceType());
        $this->assertEquals(Type::string()->nullable(), $member->getResultingType());;
        $this->assertSame($prop, $member->getProperty());
        $this->assertSame('prop', $member->asString());

        $getter = $member->createArrayGetterCallable();
        $this->assertInternalType('callable', $getter);
        $this->assertSame(TestEntity::class, (new \ReflectionFunction($getter))->getClosureScopeClass()->getName());

        $this->assertSame([1 => 'abc'], $getter([1 => new TestEntity(null, 'abc')]));
        $this->assertSame([1 => null], $getter([1 => null]));
    }
}