<?php

namespace Dms\Core\Tests\Model\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Criteria\Member\CollectionCountMethodExpression;
use Dms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Dms\Core\Model\Criteria\MemberExpressionNode;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Tests\Model\Fixtures\SubObject;
use Dms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadCriteriaTest extends CmsTestCase
{
    public function testNewCriteria()
    {
        $criteria = new LoadCriteria(TestEntity::definition());

        $this->assertSame([], $criteria->getAliasNestedMemberMap());
    }

    public function testLoadProperty()
    {
        $criteria = new LoadCriteria(TestEntity::definition());

        $criteria->load('prop');

        $this->assertEquals(
                ['prop' => new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)])],
                $criteria->getAliasNestedMemberMap()
        );
    }

    public function testLoadPropertyWithAlias()
    {
        $criteria = new LoadCriteria(TestEntity::definition());

        $criteria->load('prop', 'alias');

        $this->assertEquals([
                'alias' => new NestedMember([
                        new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)
                ])
        ],
                $criteria->getAliasNestedMemberMap());
    }

    public function testLoadAllProperties()
    {
        $criteria = new LoadCriteria(TestEntity::definition());

        $criteria->loadAll([
                'prop',
                'object',
                'object.prop' => 'sub-prop'
        ]);

        $this->assertEquals([
                'prop'     => new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)]),
                'object'   => new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('object'), false)]),
                'sub-prop' => new NestedMember([
                        new MemberPropertyExpression(TestEntity::definition()->getProperty('object'), false),
                        new MemberPropertyExpression(SubObject::definition()->getProperty('prop'), true),
                ]),
        ], $criteria->getAliasNestedMemberMap());
    }

    public function testAliasMemberTree()
    {
        $criteria = new LoadCriteria(TestEntity::definition());

        $criteria->loadAll([
                'prop',
                'object',
                'object.prop'            => 'sub-prop',
                'object.numbers'         => 'sub-numbers',
                'object.numbers.count()' => 'sub-numbers-count',
        ]);

        $this->assertEquals([
                new MemberExpressionNode(
                        new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false),
                        [],
                        ['prop']
                ),
                new MemberExpressionNode(
                        new MemberPropertyExpression(TestEntity::definition()->getProperty('object'), false),
                        [
                                new MemberExpressionNode(
                                        new MemberPropertyExpression(SubObject::definition()->getProperty('prop'), true),
                                        [],
                                        ['sub-prop']
                                ),
                                new MemberExpressionNode(
                                        new MemberPropertyExpression(SubObject::definition()->getProperty('numbers'), true),
                                        [
                                                new MemberExpressionNode(
                                                        new CollectionCountMethodExpression(
                                                                SubObject::definition()->getProperty('numbers')->getType()->nullable()
                                                        ),
                                                        [],
                                                        ['sub-numbers-count']
                                                ),
                                        ],
                                        ['sub-numbers']
                                ),
                        ],
                        ['object']
                ),
        ], $criteria->getAliasMemberTree());
    }
}