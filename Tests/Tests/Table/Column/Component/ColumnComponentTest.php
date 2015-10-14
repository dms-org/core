<?php

namespace Iddigital\Cms\Core\Tests\Table\Column\Component;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Column\Component\ColumnComponent;
use Iddigital\Cms\Core\Table\Column\Component\Type\ColumnComponentType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponentTest extends CmsTestCase
{
    public function testNewComponent()
    {
        $component = new ColumnComponent(
                'name',
                'Label',
                $type = ColumnComponentType::forField(Field::name('component')->label('Component')->int()->build())
        );

        $this->assertSame('name', $component->getName());
        $this->assertSame('Label', $component->getLabel());
        $this->assertNotEquals($type, $component->getType());
        $this->assertTrue($type->equals($component->getType()));
        $this->assertSame('name', $component->getType()->getOperator('=')->getField()->getName());
        $this->assertSame('Label', $component->getType()->getOperator('=')->getField()->getLabel());
    }

    public function testForField()
    {
        $component = ColumnComponent::forField($field = Field::name('component')->label('Component')->int()->build());

        $this->assertSame('component', $component->getName());
        $this->assertSame('Component', $component->getLabel());
        $this->assertEquals(ColumnComponentType::forField($field), $component->getType());
    }
}