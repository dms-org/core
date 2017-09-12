<?php

namespace Dms\Core\Tests\Table\Chart\Structure;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Column\Component\ColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartAxisTest extends CmsTestCase
{
    public function testNew()
    {
        $axis = new ChartAxis('name', 'Label', [
                $component = ColumnComponent::forField(Field::name('component')->label('Component')->string()->build())
        ]);

        $this->assertSame('name', $axis->getName());
        $this->assertSame('Label', $axis->getLabel());
        $this->assertTrue($component->getType()->equals($axis->getType()));
        $this->assertSame('name', $axis->getType()->getOperator('=')->getField()->getName());
        $this->assertSame('Label', $axis->getType()->getOperator('=')->getField()->getLabel());
        $this->assertSame(['component' => $component], $axis->getComponents());
        $this->assertSame(true, $axis->hasComponent('component'));
        $this->assertSame(false, $axis->hasComponent('non-existent'));
        $this->assertSame($component, $axis->getComponent('component'));
        $this->assertThrows(function () use ($axis) {
            $axis->getComponent('non-existent');
        }, InvalidArgumentException::class);
    }

    public function testAxisWithComponentsOfMultipleTypesThrows()
    {
        $this->expectException(InvalidArgumentException::class);

        new ChartAxis('name', 'Label', [
                ColumnComponent::forField(Field::name('component')->label('Component')->string()->build()),
                ColumnComponent::forField(Field::name('another')->label('Another')->int()->build()),
        ]);
    }

    public function testFromComponent()
    {
        $axis = ChartAxis::fromComponent(
                $component = ColumnComponent::forField(Field::name('component')->label('Component')->string()->build())
        );

        $this->assertSame('component', $axis->getName());
        $this->assertSame('Component', $axis->getLabel());
        $this->assertEquals($component->getType(), $axis->getType());
        $this->assertSame(['component' => $component], $axis->getComponents());
    }

    public function testForField()
    {
        $axis = ChartAxis::forField(
                $field = Field::name('component')->label('Component')->string()->build()
        );

        $this->assertSame('component', $axis->getName());
        $this->assertSame('Component', $axis->getLabel());
        $this->assertEquals($field->getType()->getProcessedPhpType(), $axis->getType()->getPhpType());
        $this->assertEquals(['component' => ColumnComponent::forField($field)], $axis->getComponents());
    }
}