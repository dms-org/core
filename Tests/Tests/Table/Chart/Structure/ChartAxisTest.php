<?php

namespace Iddigital\Cms\Core\Tests\Table\Chart\Structure;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Chart\Structure\ChartAxis;
use Iddigital\Cms\Core\Table\Column\Component\ColumnComponent;

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
        $this->assertSame($component->getType(), $axis->getType());
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
        $this->setExpectedException(InvalidArgumentException::class);

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
        $this->assertSame($component->getType(), $axis->getType());
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