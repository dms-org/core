<?php

namespace Iddigital\Cms\Core\Tests\Table\Column\Component\Type;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Table\Column\Component\Type\ColumnComponentOperator;

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
        $this->setExpectedException(InvalidArgumentException::class);
        new ColumnComponentOperator('invalid-operator', $this->getMockForAbstractClass(IField::class));
    }
}