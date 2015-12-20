<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Model\Object\PropertyAccessibility;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Model\Object\Fixtures\TestAbstractTypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AbstractTypedObjectTest extends CmsTestCase
{
    public function testClassDefinition()
    {
        $definition = TestAbstractTypedObject::definition();

        $this->assertTrue($definition->isAbstract());
        $this->assertNull($definition->getCleanInstance());
        $this->assertNull($definition->newCleanInstance());
        $this->assertEquals(
                [
                        'foo' => new FinalizedPropertyDefinition(
                                'foo',
                                Type::string(),
                                'bar',
                                new PropertyAccessibility(PropertyAccessibility::ACCESS_PUBLIC, TestAbstractTypedObject::class), false
                        )
                ],
                $definition->getProperties()
        );
    }
}