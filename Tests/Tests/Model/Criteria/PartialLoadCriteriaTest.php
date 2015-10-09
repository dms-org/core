<?php

namespace Iddigital\Cms\Core\Tests\Model\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Criteria\NestedProperty;
use Iddigital\Cms\Core\Model\Criteria\PartialLoadCriteria;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PartialLoadCriteriaTest extends CmsTestCase
{
    public function testNewCriteria()
    {
        $criteria = new PartialLoadCriteria(TestEntity::definition());

        $this->assertSame([], $criteria->getAliasNestedPropertyMap());
    }

    public function testLoadProperty()
    {
        $criteria = new PartialLoadCriteria(TestEntity::definition());

        $criteria->load('prop');

        $this->assertEquals(['prop' => NestedProperty::parsePropertyName(TestEntity::definition(), 'prop')],
                $criteria->getAliasNestedPropertyMap());
    }

    public function testLoadPropertyWithAlias()
    {
        $criteria = new PartialLoadCriteria(TestEntity::definition());

        $criteria->load('prop', 'alias');

        $this->assertEquals(['alias' => NestedProperty::parsePropertyName(TestEntity::definition(), 'prop')],
                $criteria->getAliasNestedPropertyMap());
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
                'prop'     => NestedProperty::parsePropertyName(TestEntity::definition(), 'prop'),
                'object'   => NestedProperty::parsePropertyName(TestEntity::definition(), 'object'),
                'sub-prop' => NestedProperty::parsePropertyName(TestEntity::definition(), 'object.prop'),
        ], $criteria->getAliasNestedPropertyMap());
    }
}