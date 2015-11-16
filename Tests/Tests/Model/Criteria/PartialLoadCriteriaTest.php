<?php

namespace Iddigital\Cms\Core\Tests\Model\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\Criteria\PartialLoadCriteria;
use Iddigital\Cms\Core\Tests\Model\Fixtures\SubObject;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PartialLoadCriteriaTest extends CmsTestCase
{
    public function testNewCriteria()
    {
        $criteria = new PartialLoadCriteria(TestEntity::definition());

        $this->assertSame([], $criteria->getAliasNestedMemberMap());
    }

    public function testLoadProperty()
    {
        $criteria = new PartialLoadCriteria(TestEntity::definition());

        $criteria->load('prop');

        $this->assertEquals(
                ['prop' => new NestedMember([new MemberPropertyExpression(TestEntity::definition()->getProperty('prop'), false)])],
                $criteria->getAliasNestedMemberMap()
        );
    }

    public function testLoadPropertyWithAlias()
    {
        $criteria = new PartialLoadCriteria(TestEntity::definition());

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
        $criteria = new PartialLoadCriteria(TestEntity::definition());

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
}