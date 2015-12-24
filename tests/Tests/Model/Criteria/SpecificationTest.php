<?php

namespace Dms\Core\Tests\Model\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Criteria\Condition\AndCondition;
use Dms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Dms\Core\Model\Criteria\Condition\MemberCondition;
use Dms\Core\Model\Criteria\Condition\NotCondition;
use Dms\Core\Model\Criteria\Condition\OrCondition;
use Dms\Core\Model\Criteria\InvalidMemberExpressionException;
use Dms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Tests\Model\Criteria\Fixtures\MockSpecification;
use Dms\Core\Tests\Model\Fixtures\SubclassEntity;
use Dms\Core\Tests\Model\Fixtures\SubObject;
use Dms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SpecificationTest extends CmsTestCase
{
    public function testEmptySpecThrowsException()
    {
        $this->setExpectedException(InvalidOperationException::class);

        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {

        });
    }

    public function testNewSpecificationWithCondition()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
        });

        $this->assertSame(TestEntity::definition(), $spec->getClass());
        $this->assertEquals(new MemberCondition(
                new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)]),
                '=',
                'foo'),
                $spec->getCondition());
    }

    public function testValidVerifyClass()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
        });

        $spec->verifyOfClass(TestEntity::class);
    }

    public function testInvalidVerifyClass()
    {
        $this->setExpectedException(TypeMismatchException::class);
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
        });

        $spec->verifyOfClass(\stdClass::class);
    }

    public function testValidPropertyCondition()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
        });

        $this->assertEquals(
                new MemberCondition(
                        new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)]),
                        '=',
                        'foo'
                )
                , $spec->getCondition());
    }

    public function testValidNestedPropertyCondition()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('object.prop', '=', 'foo');
        });

        $this->assertEquals(
                new MemberCondition(
                        new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('object'), false),
                                new MemberPropertyExpression(SubObject::definition()->getProperty('prop'), true),
                        ]),
                        '=',
                        'foo')
                , $spec->getCondition());
    }

    public function testValidInstanceOfCondition()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->whereInstanceOf(TestEntity::class);
        });

        $this->assertEquals(new InstanceOfCondition(TestEntity::class), $spec->getCondition());
    }

    public function testInvalidPropertyCondition()
    {
        $this->setExpectedException(InvalidMemberExpressionException::class);

        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('some_invalid_property', '=', 'foo');
        });
    }

    public function testAsCriteria()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
            $match->whereInstanceOf(TestEntity::class);
        });

        $criteria = $spec->asCriteria();

        $this->assertEquals(new AndCondition([
                new MemberCondition(
                        new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)]),
                        '=',
                        'foo'
                ),
                new InstanceOfCondition(TestEntity::class)
        ]), $criteria->getCondition());
    }

    public function testSatisfiedByCondition()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('object.prop', '=', 'foo');
        });

        $this->assertTrue($spec->isSatisfiedBy(new TestEntity(null, '', new SubObject('foo'))));
        $this->assertFalse($spec->isSatisfiedBy(new TestEntity(null, '', new SubObject('bar'))));
        $this->assertFalse($spec->isSatisfiedBy(new TestEntity(null, '', null)));
    }

    public function testSatisfiedByWithInstanceOf()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->whereInstanceOf(SubclassEntity::class);
        });

        $this->assertTrue($spec->isSatisfiedBy(new SubclassEntity()));
        $this->assertFalse($spec->isSatisfiedBy(new TestEntity()));
    }

    public function testWrongObjectType()
    {
        $this->setExpectedException(TypeMismatchException::class);

        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
        });

        $spec->isSatisfiedBy(new SubObject());
    }

    public function testAnd()
    {
        $spec1 = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
        });

        $spec2 = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '!=', 'bar');
        });

        $this->assertEquals(
                new AndCondition([
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '=', 'foo'),
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'bar'),
                ]),
                $spec1->and_($spec2)->getCondition()
        );

        $this->assertThrows(function () {
            $spec1 = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
                $match->where('prop', '=', 'foo');
            });

            $spec2 = new MockSpecification(SubObject::class, function (SpecificationDefinition $match) {
                $match->where('prop', '!=', 'bar');
            });

            $spec1->and_($spec2);
        }, TypeMismatchException::class);
    }

    public function testOr()
    {
        $spec1 = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
        });

        $spec2 = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '!=', 'bar');
        });

        $this->assertEquals(
                new OrCondition([
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '=', 'foo'),
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'bar'),
                ]),
                $spec1->or_($spec2)->getCondition()
        );

        $this->assertThrows(function () {
            $spec1 = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
                $match->where('prop', '=', 'foo');
            });

            $spec2 = new MockSpecification(SubObject::class, function (SpecificationDefinition $match) {
                $match->where('prop', '!=', 'bar');
            });

            $spec1->or_($spec2);
        }, TypeMismatchException::class);
    }

    public function testNot()
    {
        $spec1 = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('prop', '=', 'foo');
        });

        $this->assertEquals(
                new NotCondition(
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '=', 'foo')
                ),
                $spec1->not()->getCondition()
        );
    }
}