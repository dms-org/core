<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Model\Object\PropertyAccessibility;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\TestAbstractTypedObject;

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