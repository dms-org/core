<?php

namespace Iddigital\Cms\Core\Tests\Model\Criteria\Member;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Model\Criteria\Member\MemberMethodExpression;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberMethodExpressionTest extends CmsTestCase
{
    public function testNewNonNullable()
    {
        $member = new MemberMethodExpression(TestEntity::type(), 'getProp', [], Type::string());

        $this->assertEquals(TestEntity::type(), $member->getSourceType());
        $this->assertEquals(Type::string(), $member->getResultingType());;
        $this->assertSame('getProp()', $member->asString());

        $getter = $member->createGetterCallable();

        $this->assertSame('abc', $getter(new TestEntity(null, 'abc')));
    }

    public function testNewNullable()
    {
        $member = new MemberMethodExpression(TestEntity::type()->nullable(), 'getProp', [], Type::string());

        $this->assertEquals(TestEntity::type()->nullable(), $member->getSourceType());
        $this->assertEquals(Type::string()->nullable(), $member->getResultingType());;
        $this->assertSame('getProp()', $member->asString());

        $getter = $member->createGetterCallable();

        $this->assertSame('abc', $getter(new TestEntity(null, 'abc')));
        $this->assertSame(null, $getter(null));
    }

    public function testNonExistent()
    {
        $member = new MemberMethodExpression(TestEntity::type()->nullable(), 'nonExistent', ['abc', 'sss'], Type::string());

        $this->assertEquals(TestEntity::type()->nullable(), $member->getSourceType());
        $this->assertEquals(Type::string()->nullable(), $member->getResultingType());;
        $this->assertSame('nonExistent(abc,sss)', $member->asString());

        $this->assertThrows(function () use ($member) {
            $member->createGetterCallable();
        }, NotImplementedException::class);
    }
}