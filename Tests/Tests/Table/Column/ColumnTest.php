<?php

namespace Iddigital\Cms\Core\Tests\Table\Column;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Column\Column;
use Iddigital\Cms\Core\Table\Column\Component\ColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnTest extends CmsTestCase
{
    public function testNewColumnWithSingleComponent()
    {
        $column = new Column(
                'name',
                'Label',
                [$component = ColumnComponent::forField(Field::name('component')->label('Component')->int()->build())]
        );

        $this->assertSame('name', $column->getName());
        $this->assertSame('Label', $column->getLabel());
        $this->assertSame(['component'], $column->getComponentNames());
        $this->assertSame(['component' => $component], $column->getComponents());
        $this->assertSame(true, $column->hasComponent('component'));
        $this->assertSame(false, $column->hasComponent('other-component'));
        $this->assertSame(true, $column->hasSingleComponent());
        $this->assertSame($component, $column->getComponent('component'));
        $this->assertSame($component, $column->getComponent());
        $this->assertSame('name.component', $column->getComponentId('component'));
        $this->assertSame('name.component', $column->getComponentId());

        $this->assertThrows(function () use ($column) {
            $column->getComponentId('non-existent');
        }, InvalidArgumentException::class);
    }

    public function testNewColumnWithMultipleComponent()
    {
        $column = new Column(
                'name',
                'Label',
                [
                        $string = ColumnComponent::forField(Field::name('string')->label('String')->string()->build()),
                        $int = ColumnComponent::forField(Field::name('int')->label('Int')->int()->build()),
                ]
        );

        $this->assertSame('name', $column->getName());
        $this->assertSame('Label', $column->getLabel());
        $this->assertSame(['string', 'int'], $column->getComponentNames());
        $this->assertSame(['string' => $string, 'int' => $int], $column->getComponents());
        $this->assertSame(true, $column->hasComponent('string'));
        $this->assertSame(false, $column->hasComponent('other-component'));
        $this->assertSame(false, $column->hasSingleComponent());
        $this->assertSame($string, $column->getComponent('string'));
        $this->assertSame($int, $column->getComponent('int'));
        $this->assertSame('name.string', $column->getComponentId('string'));
        $this->assertSame('name.int', $column->getComponentId('int'));

        $this->assertThrows(function () use ($column) {
            $column->getComponent();
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($column) {
            $column->getComponent('non-existent');
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($column) {
            $column->getComponentId();
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($column) {
            $column->getComponentId('non-existent');
        }, InvalidArgumentException::class);
    }

    public function testInvalidComponent()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new Column('name', 'Label', [1]);
    }
}