<?php

namespace Dms\Core\Tests\Table\Builder;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Column\Column as TableColumn;
use Dms\Core\Table\Column\Component\ColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnBuilderTest extends CmsTestCase
{
    public function testFrom()
    {
        $field = Field::name('field_name')->label('Field Label')->string();

        $expectedColumn = new TableColumn('field_name', 'Field Label', false, [ColumnComponent::forField($field->build())]);

        $this->assertEquals($expectedColumn, Column::from($field));
        $this->assertEquals($expectedColumn, Column::from($field->build()));
        $this->assertEquals($expectedColumn, Column::from(ColumnComponent::forField($field->build())));
    }

    public function testInvalidFrom()
    {
        $this->expectException(TypeMismatchException::class);

        Column::from(new \stdClass());
    }

    public function testChainedMethods()
    {
        $column = Column::name('column_name')->label('Column Label')->hidden()->components([
                Field::name('some_string')->label('Text')->string(),
                Field::name('some_int')->label('Number')->int()->build(),
                ColumnComponent::forField(Field::name('float')->label('Number')->decimal()->build())
        ]);

        $this->assertEquals(
                new TableColumn('column_name', 'Column Label', true, [
                        ColumnComponent::forField(Field::name('some_string')->label('Text')->string()->build()),
                        ColumnComponent::forField(Field::name('some_int')->label('Number')->int()->build()),
                        ColumnComponent::forField(Field::name('float')->label('Number')->decimal()->build()),
                ]),
                $column
        );
    }

    public function testChainedMethodWithInvalidValue()
    {
        $this->expectException(TypeMismatchException::class);

        Column::name('column_name')->label('Column Label')->components([
                null
        ]);
    }
}