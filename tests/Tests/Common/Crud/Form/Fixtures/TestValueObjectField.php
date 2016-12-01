<?php

namespace Dms\Core\Tests\Common\Crud\Form\Fixtures;

use Dms\Core\Common\Crud\Definition\Form\ValueObjectFieldDefinition;
use Dms\Core\Common\Crud\Form\ValueObjectField;
use Dms\Core\Form\Field\Builder\Field;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestValueObjectField extends ValueObjectField
{
    /**
     * @param ValueObjectFieldDefinition $form
     */
    public function define(ValueObjectFieldDefinition $form)
    {
        $form->bindTo(TestValueObject::class);

        $form->section('Details', [
            $form->field(
                Field::create()->name('string')->label('String')->string()->required()
            )->bindToProperty(TestValueObject::STRING),
            //
            $form->field(
                Field::create()->name('int')->label('Int')->int()->required()
            )->bindToProperty(TestValueObject::INT),
        ]);
    }
}