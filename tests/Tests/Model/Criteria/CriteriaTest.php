<?php

namespace Dms\Core\Tests\Model\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Criteria\Condition\AndCondition;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Dms\Core\Model\Criteria\Condition\MemberCondition;
use Dms\Core\Model\Criteria\Condition\NotCondition;
use Dms\Core\Model\Criteria\Condition\OrCondition;
use Dms\Core\Model\Criteria\CustomSpecification;
use Dms\Core\Model\Criteria\InvalidMemberExpressionException;
use Dms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Dms\Core\Model\Criteria\MemberOrdering;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Tests\Model\Criteria\Fixtures\MockSpecification;
use Dms\Core\Tests\Model\Fixtures\SubObject;
use Dms\Core\Tests\Model\Fixtures\TestEntity;

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
            ),
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
            ]), OrderingDirection::ASC),
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
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false),
                ]), '!=', 'foo'),
                new MemberCondition(new NestedMember([
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false),
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
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false),
                ]), '!=', 'foo'),
                new MemberCondition(new NestedMember([
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false),
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
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false),
                ]), '!=', 'foo'),
                new AndCondition([
                    new MemberCondition(new NestedMember([
                        new MemberPropertyExpression(TestEntity::definition()
                            ->getProperty('prop'), false),
                    ]), '!=', 'foo'),
                    new MemberCondition(new NestedMember([
                        new MemberPropertyExpression(TestEntity::definition()
                            ->getProperty('prop'), false),
                    ]), '!=', 'bar'),
                ]),
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
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false),
                ]), '!=', 'foo'),
                new MemberCondition(new NestedMember([
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false),
                ]), '!=', 'bar'),
            ])),
            $criteria->getCondition());
    }

    public function testThrowsExceptionForInvalidOperatorWithNull()
    {
        $criteria = TestEntity::criteria();

        // = and != are value
        $criteria->where('object', '!=', null);
        $criteria->where('object', '=', null);

        $this->assertThrows(function () use ($criteria) {
            $criteria->where('object', '>', null);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($criteria) {
            $criteria->where('object', '<=', null);
        }, InvalidArgumentException::class);
    }

    public function testWherePropertySatisfies()
    {
        $criteria = TestEntity::criteria();

        $specification = new CustomSpecification(
            SubObject::class,
            function (SpecificationDefinition $match) {
                $match->whereStringContainsCaseInsensitive('prop', 'foo');

                $match->whereNot(function (SpecificationDefinition $match) {
                    $match->where('number', '>', 10);
                });

                $match->whereAny(function (SpecificationDefinition $match) {
                    $match->where('number', '=', 20);
                    $match->where('number', '=', 30);
                    $match->where('number', '=', 40);
                });
            }
        );

        $criteria->whereMemberSatisfies('object', $specification);

        $this->assertEquals(
            TestEntity::criteria()
                ->whereStringContainsCaseInsensitive('object.prop', 'foo')
                ->whereNot(function (SpecificationDefinition $match) {
                    $match->where('object.number', '>', 10);
                })
                ->whereAny(function (SpecificationDefinition $match) {
                    $match->where('object.number', '=', 20);
                    $match->where('object.number', '=', 30);
                    $match->where('object.number', '=', 40);
                })
                ->getCondition(),
            $criteria->getCondition()
        );
    }

    public function testWhereHasAll()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereHasAll('objects', $spec = SubObject::specification(function (SpecificationDefinition $match) {
            $match->where('number', '>', 5);
        }));

        $this->assertEquals(
            new MemberCondition(
                new NestedMember([
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('objects'), false),
                ]),
                ConditionOperator::ALL_SATISFIES,
                $spec
            ),
            $criteria->getCondition());
    }

    public function testWhereHasAny()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereHasAny('objects', $spec = SubObject::specification(function (SpecificationDefinition $match) {
            $match->where('number', '>', 5);
        }));

        $this->assertEquals(
            new MemberCondition(
                new NestedMember([
                    new MemberPropertyExpression(TestEntity::definition()->getProperty('objects'), false),
                ]),
                ConditionOperator::ANY_SATISFIES,
                $spec
            ),
            $criteria->getCondition());
    }

    public function testMergeCriteriaConditions()
    {
        $criteria1 = TestEntity::criteria();
        $criteria2 = TestEntity::criteria();

        $criteria1->where('prop', '=', 'foo');
        $criteria2->where('prop', '!=', 'abc');

        $this->assertEquals(
            TestEntity::criteria()
                ->where('prop', '=', 'foo')
                ->where('prop', '!=', 'abc'),
            $criteria1->merge($criteria2)
        );

        $this->assertEquals(
            TestEntity::criteria()
                ->where('prop', '=', 'foo'),
            $criteria1->merge(TestEntity::criteria())
        );

        $this->assertEquals(
            TestEntity::criteria()
                ->where('prop', '=', 'foo'),
            TestEntity::criteria()->merge($criteria1)
        );
    }

    public function testMergeCriteriaOrderings()
    {
        $criteria1 = TestEntity::criteria();
        $criteria2 = TestEntity::criteria();

        $criteria1->orderByAsc('prop');
        $criteria2->orderByDesc('prop');

        $this->assertEquals(
            TestEntity::criteria()
                ->orderByDesc('prop')
                ->orderByAsc('prop'),
            $criteria1->merge($criteria2)
        );

        $this->assertEquals(
            TestEntity::criteria()
                ->orderByAsc('prop'),
            $criteria1->merge(TestEntity::criteria())
        );

        $this->assertEquals(
            TestEntity::criteria()
                ->orderByAsc('prop'),
            TestEntity::criteria()->merge($criteria1)
        );
    }

    public function testMergeCriteriaStartAndOffset()
    {
        $criteria1 = TestEntity::criteria();
        $criteria2 = TestEntity::criteria();

        $criteria1->skip(2)->limit(10);
        $criteria2->skip(5)->limit(20);

        $this->assertEquals(
            TestEntity::criteria()
                ->skip(7)
                ->limit(10),
            $criteria1->merge($criteria2)
        );

        $this->assertEquals(
            TestEntity::criteria()
                ->skip(2)->limit(10),
            $criteria1->merge(TestEntity::criteria())
        );

        $this->assertEquals(
            TestEntity::criteria()
                ->skip(2)->limit(10),
            TestEntity::criteria()->merge($criteria1)
        );
    }

    public function testAsSpecification()
    {
        $criteria = TestEntity::criteria();

        $criteria->where('prop', '=', 'foo');

        $this->assertEquals(
            $criteria,
            $criteria->asSpecification()->asCriteria()
        );

        $this->assertSame($criteria->getCondition(), $criteria->asSpecification()->getCondition());
    }

    public function testEmptyWhereAny()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereAny(function () {

        });

        $this->assertNull($criteria->getCondition());
    }

    public function testEmptyWhereAll()
    {
        $criteria = TestEntity::criteria();

        $criteria->whereAll(function () {

        });

        $this->assertNull($criteria->getCondition());
    }
}