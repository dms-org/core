<?php

namespace Dms\Core\Tests\Table\Column\Component\Type;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IField;
use Dms\Core\Table\Column\Component\Type\ColumnComponentOperator;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponentOperatorTest extends CmsTestCase
{
    public function testNewOperator()
    {
        $field    = Field::name('foo')->label('Foo')->string()->build();
        $operator = new ColumnComponentOperator('!=', $field);

        $this->assertSame('!=', $operator->getOperator());
        $this->assertSame($field, $operator->getField());
    }

    public function testInvalidOperatorString()
    {
        $this->expectException(InvalidArgumentException::class);
        new ColumnComponentOperator('invalid-operator', $this->getMockForAbstractClass(IField::class));
    }

    public function testWithFieldAs()
    {
        $operator = new ColumnComponentOperator('=', Field::name('foo')->label('Foo')->string()->build());
        $newOperator = $operator->withFieldAs('bar', 'Bar');

        $this->assertSame('foo', $operator->getField()->getName());
        $this->assertSame('Foo', $operator->getField()->getLabel());

        $this->assertSame('bar', $newOperator->getField()->getName());
        $this->assertSame('Bar', $newOperator->getField()->getLabel());
    }
}