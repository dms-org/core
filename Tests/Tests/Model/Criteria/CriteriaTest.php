<?php

namespace Iddigital\Cms\Core\Tests\Model\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Criteria\Condition\AndCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\MemberCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\NotCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\OrCondition;
use Iddigital\Cms\Core\Model\Criteria\InvalidMemberExpressionException;
use Iddigital\Cms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Iddigital\Cms\Core\Model\Criteria\MemberOrdering;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\Criteria\OrderingDirection;
use Iddigital\Cms\Core\Model\Criteria\SpecificationDefinition;
use Iddigital\Cms\Core\Tests\Model\Criteria\Fixtures\MockSpecification;
use Iddigital\Cms\Core\Tests\Model\Fixtures\SubObject;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaTest extends CmsTestCase
{
    public function testNewCriteria()
    {
        $criteria = TestEntity::criteria();

        $this->assertSame(TestEntity::definition(), $criteria->getClass());
        $this->assertSame(false, $criteria->hasCondition());
        $this->assertSame(null, $criteria->getCondition());
        $this->assertSame(false, $criteria->hasOrderings());
        $this->assertSame([], $criteria->getOrderings());
        $this->assertSame(0, $criteria->getStartOffset());
        $this->assertSame(null, $criteria->getLimitAmount());
        $this->assertSame(false, $criteria->hasLimitAmount());
    }

    public function testValidVerifyClass()
    {
        $criteria = TestEntity::criteria();

        $criteria->verifyOfClass(TestEntity::class);
    }

    public function testInvalidVerifyClass()
    {
        $this->setExpectedException(TypeMismatchException::class);
        $criteria = TestEntity::criteria();

        $criteria->verifyOfClass(\stdClass::class);
    }

    public function testValidPropertyCondition()
    {
        $criteria = TestEntity::criteria();

        $criteria->where('prop', '=', 'foo');

        $this->assertSame(TestEntity::definition(), $criteria->getClass());
        $this->assertEquals(
                new MemberCondition(
                        new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)]),
                        '=',
                        'foo'
                ),
                $criteria->getCondition()
        );
    }

    public function testValidNestedPropertyCondition()
    {
        $criteria = TestEntity::criteria();

        $criteria->where('object.prop', '=', 'foo');

        $this->assertSame(TestEntity::definition(), $criteria->getClass());
        $this->assertEquals(
                new MemberCondition(
                        new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('object'), false),
                                new MemberPropertyExpression(SubObject::definition()->getProperty('prop'), true),
                        ]),
                        '=',
                        'foo'
                ),
                $criteria->getCondition());
    }

    public function testValidInstanceOfCondition()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereInstanceOf(TestEntity::class);

        $this->assertEquals(
                new InstanceOfCondition(TestEntity::class),
                $criteria->getCondition());
        $this->assertSame(TestEntity::definition(), $criteria->getClass());
    }

    public function testInvalidMemberExpression()
    {
        $this->setExpectedException(InvalidMemberExpressionException::class);
        $criteria = TestEntity::criteria();

        $criteria->where('some_invalid_property', '=', 'foo');
    }

    public function testValidOrderBy()
    {
        $criteria = TestEntity::criteria();

        $criteria->orderByAsc('prop');

        $this->assertSame(TestEntity::definition(), $criteria->getClass());
        $this->assertEquals([
                new MemberOrdering(
                        new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)]),
                        OrderingDirection::ASC
                )
        ], $criteria->getOrderings());
        $this->assertSame(true, $criteria->hasOrderings());
    }

    public function testValidNestedOrderBy()
    {
        $criteria = TestEntity::criteria();

        $criteria->orderByAsc('object.prop');

        $this->assertSame(TestEntity::definition(), $criteria->getClass());
        $this->assertEquals([
                new MemberOrdering(new NestedMember([
                        new MemberPropertyExpression(TestEntity::definition()->getProperty('object'), false),
                        new MemberPropertyExpression(SubObject::definition()->getProperty('prop'), true),
                ]), OrderingDirection::ASC)
        ], $criteria->getOrderings());
        $this->assertSame(true, $criteria->hasOrderings());
    }

    public function testInvalidOrderBy()
    {
        $this->setExpectedException(InvalidMemberExpressionException::class);
        $criteria = TestEntity::criteria();

        $criteria->orderByAsc('some_invalid_property');
    }

    public function testSkipAndLimit()
    {
        $criteria = TestEntity::criteria();

        $criteria->skip(5)->limit(10);

        $this->assertSame(5, $criteria->getStartOffset());
        $this->assertSame(10, $criteria->getLimitAmount());
        $this->assertSame(true, $criteria->hasLimitAmount());
    }

    public function testValidNestedPropertyConditionWithSpecification()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereSatisfies(new MockSpecification(
                TestEntity::class,
                function (SpecificationDefinition $match) {
                    $match->where('object.prop', '=', 'foo');
                }
        ));

        $this->assertSame(TestEntity::definition(), $criteria->getClass());
        $this->assertEquals(
                new MemberCondition(new NestedMember([
                        new MemberPropertyExpression(TestEntity::definition()->getProperty('object'), false),
                        new MemberPropertyExpression(SubObject::definition()->getProperty('prop'), true),
                ]),
                        '=',
                        'foo')
                , $criteria->getCondition());
    }

    public function testValidInstanceOfConditionWithSpecification()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereSatisfies(new MockSpecification(
                TestEntity::class,
                function (SpecificationDefinition $match) {
                    $match->whereInstanceOf(TestEntity::class);
                }
        ));

        $this->assertSame(TestEntity::definition(), $criteria->getClass());
        $this->assertEquals(new InstanceOfCondition(TestEntity::class), $criteria->getCondition());
    }

    public function testWhereSatisfiesWrongClass()
    {
        $this->setExpectedException(TypeMismatchException::class);

        $criteria = TestEntity::criteria();

        $criteria->whereSatisfies(new MockSpecification(
                SubObject::class,
                function (SpecificationDefinition $match) {
                    $match->whereInstanceOf(SubObject::class);
                }
        ));
    }

    public function testWhereAll()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereAll(function (SpecificationDefinition $match) {
            $match->where('prop', '!=', 'foo');
            $match->where('prop', '!=', 'bar');
        });

        $this->assertEquals(
                new AndCondition([
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'foo'),
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'bar'),
                ]),
                $criteria->getCondition());
    }

    public function testWhereAny()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereAny(function (SpecificationDefinition $match) {
            $match->where('prop', '!=', 'foo');
            $match->where('prop', '!=', 'bar');
        });

        $this->assertEquals(
                new OrCondition([
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'foo'),
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'bar'),
                ]),
                $criteria->getCondition());
    }

    public function testWhereAndAll()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereAny(function (SpecificationDefinition $match) {
            $match->where('prop', '!=', 'foo');
            $match->whereAll(function (SpecificationDefinition $match) {
                $match->where('prop', '!=', 'foo');
                $match->where('prop', '!=', 'bar');
            });
        });

        $this->assertEquals(
                new OrCondition([
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'foo'),
                        new AndCondition([
                                new MemberCondition(new NestedMember([
                                        new MemberPropertyExpression(TestEntity::definition()
                                                ->getProperty('prop'), false)
                                ]), '!=', 'foo'),
                                new MemberCondition(new NestedMember([
                                        new MemberPropertyExpression(TestEntity::definition()
                                                ->getProperty('prop'), false)
                                ]), '!=', 'bar'),
                        ])
                ]),
                $criteria->getCondition());
    }

    public function testWhereNot()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereNot(function (SpecificationDefinition $match) {
            $match->where('prop', '!=', 'foo');
            $match->where('prop', '!=', 'bar');
        });

        $this->assertEquals(
                new NotCondition(new AndCondition([
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'foo'),
                        new MemberCondition(new NestedMember([
                                new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                        ]), '!=', 'bar'),
                ])),
                $criteria->getCondition());
    }
}