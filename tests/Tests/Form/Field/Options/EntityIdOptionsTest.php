<?php

namespace Dms\Core\Tests\Form\Field\Options;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Options\FieldOption;
use Dms\Core\Tests\Form\Field\Options\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdFieldOptionsTest extends CmsTestCase
{
    public function testTryGetValues()
    {
        $options = new EntityIdOptions(
            TestEntity::collection([
                new TestEntity(1, 'a'),
                new TestEntity(2, 'b'),
                new TestEntity(3, 'c'),
            ]),
            function (TestEntity $entity) {
                return $entity->name;
            }
        );

        $this->assertSame([], $options->tryGetOptionsForValues([]));
        $this->assertEquals([new FieldOption(1, 'a')], $options->tryGetOptionsForValues([1]));
        $this->assertEquals([new FieldOption(1, 'a'), new FieldOption(2, 'b')], $options->tryGetOptionsForValues([1, 2]));
        $this->assertEquals([new FieldOption(1, 'a')], $options->tryGetOptionsForValues([1, 4]));
        $this->assertEquals([], $options->tryGetOptionsForValues([4]));
    }
}